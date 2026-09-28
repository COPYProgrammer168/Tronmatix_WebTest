<?php

// app/Http/Controllers/Api/ChatController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class ChatController extends Controller
{
    public function message(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4000',
            'session_id' => 'nullable|integer|exists:chat_sessions,id',
            'history' => 'nullable|array',
            'history.*.role' => 'required_with:history|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:4000',
        ]);

        // $user ត្រូវ define មុនគេបំផុត
        $user = Auth::user();
        $sessionId = $validated['session_id'] ?? null;
        $message = $validated['message'];
        $history = $validated['history'] ?? [];

        // Rate limit
        $rateLimitKey = $user ? 'chat:user:' . $user->id : 'chat:ip:' . $request->ip();
        $rateLimitMaxAttempts = $user ? 15 : 5;

        if (RateLimiter::remaining($rateLimitKey, $rateLimitMaxAttempts) === 0) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'reply' => "⏳ Too many messages. Please wait {$seconds} seconds.",
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 60);

        // Session
        $session = null;
        if ($sessionId) {
            $session = ChatSession::find($sessionId);
            if ($session && $user && $session->user_id !== null && $session->user_id !== $user->id) {
                return response()->json(['success' => false, 'reply' => 'Unauthorized.'], 403);
            }
        }

        if (!$session) {
            $session = ChatSession::create([
                'user_id' => $user?->id,
                'status' => 'open',
            ]);
        }

        // Save user message
        ChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'user',
            'message' => $message,
            'sent_at' => now(),
        ]);

        // Call Groq API
        $history = $this->sanitizeHistory($history);
        $reply = $this->callGroq($message, $history);

        // Save bot reply
        ChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'bot',
            'message' => $reply,
            'sent_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'session_id' => $session->id,
        ]);
    }

    // ── Sanitize conversation history ─────────────────────────────────────────

    private function sanitizeHistory(array $history): array
    {
        // Filter out invalid or empty messages
        $history = array_values(array_filter(
            $history,
            fn($h) =>
            isset($h['role'], $h['content']) &&
            in_array($h['role'], ['user', 'assistant'], true) &&
            trim($h['content']) !== ''
        ));

        $cleaned = [];
        $lastRole = null;

        foreach ($history as $msg) {
            if ($msg['role'] === $lastRole) {
                // Merge into previous instead of creating duplicate role
                $cleaned[count($cleaned) - 1]['content'] .= "\n" . $msg['content'];
            } else {
                $cleaned[] = $msg;
                $lastRole = $msg['role'];
            }
        }

        // Gemini history must start with 'user'
        while (!empty($cleaned) && $cleaned[0]['role'] === 'assistant') {
            array_shift($cleaned);
        }

        return array_values($cleaned);
    }

    /**
     * Build a product-knowledge context block for the AI.
     *
     * Matches are found by tokenizing the user message into meaningful keywords
     * and searching name / category / brand / description / specs — so asking
     * "what are the specs of the RTX 4060" surfaces the right product even when
     * the whole message isn't a single contiguous product-name substring.
     *
     * Only a few top matches are injected (plus up to 2 with the most complete
     * specs) to keep the prompt inside the model's output-token budget. Each
     * entry carries price, stock, warranty, caption, full specs (when present)
     * and description so the bot can answer spec questions from real data.
     */
    private function getProductContext(string $message): string
    {
        $terms = $this->extractKeywords($message);

        // ── 1. Score every product by keyword hits ────────────────────────────
        $scored = [];
        Product::select(['id', 'name', 'slug', 'sku', 'category', 'brand', 'caption', 'description', 'price', 'warranty', 'current_stock', 'specs', 'specs_title'])
            ->chunkById(200, function ($chunk) use ($terms, &$scored) {
                foreach ($chunk as $p) {
                    $haystack = strtolower(implode(' ', array_filter([
                        $p->name,
                        $p->category,
                        $p->brand,
                        $p->caption,
                        $p->specs_title,
                        is_string($p->specs) ? $p->specs : implode(' ', array_keys((array) $p->specs ?: [])),
                        $p->description,
                    ])));

                    $score = 0;
                    foreach ($terms as $t) {
                        if (str_contains($haystack, $t)) $score++;
                    }

                    if ($score > 0) {
                        $hasSpecs = is_array($p->specs) && count($p->specs) > 0;
                        $scored[] = ['product' => $p, 'score' => $score, 'has_specs' => $hasSpecs];
                    }
                }
            });

        // ── No exact product match → fall back to the categories the customer ─
        //    is asking about (e.g. "GPU for 1440p gaming" → VGA category) so the
        //    model still gets REAL inventory to work from instead of guessing.
        if (empty($scored)) {
            $categoryTerms = $this->matchCategories($terms, $message);

            if (! empty($categoryTerms)) {
                Product::select(['id', 'name', 'slug', 'sku', 'category', 'brand', 'caption', 'description', 'price', 'warranty', 'current_stock', 'specs', 'specs_title'])
                    ->where(function ($q) use ($categoryTerms) {
                        foreach ($categoryTerms as $c) {
                            $q->orWhereRaw('LOWER(category) = ?', [strtolower($c)]);
                        }
                    })
                    ->where(fn ($q) => $q->whereNull('current_stock')->orWhere('current_stock', '>', 0))
                    ->orderByDesc('id')
                    ->limit(4)
                    ->get()
                    ->each(function ($p) use (&$scored) {
                        $scored[] = [
                            'product'   => $p,
                            'score'     => 1,
                            'has_specs' => is_array($p->specs) && count($p->specs) > 0,
                        ];
                    });
            }
        }

        if (empty($scored)) return '';

        // Sort by score, then prefer entries that carry real specs.
        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        // Take the top 3 matches + any remaining spec-rich matches (max 5 total).
        $picked = array_slice($scored, 0, 3);
        foreach (array_slice($scored, 3) as $s) {
            if ($s['has_specs'] && count($picked) < 5) $picked[] = $s;
        }

        // ── 2. Render the context block ───────────────────────────────────────
        $context = "\n\n## PRODUCT KNOWLEDGE (from this shop's catalog — prefer these over guesswork):\n";

        foreach ($picked as $s) {
            $p    = $s['product'];
            $name = $p->name;
            $sku  = $p->sku ?: '—';
            $price = $p->price !== null && $p->price !== '' && ! preg_match('/^\$+$/', (string) $p->price)
                ? '$' . number_format((float) $p->price, 2)
                : '$$$ (ask for price)';
            $status = ($p->current_stock !== null && $p->current_stock <= 0)
                ? 'Out of stock'
                : 'In stock';

            $context .= "\n• **{$name}** (SKU `{$sku}`) — {$price} · {$status}";
            if ($p->brand)                   $context .= "\n  Brand: {$p->brand}";
            if ($p->category)                $context .= "\n  Category: {$p->category}";
            if ($p->warranty)                $context .= "\n  Warranty: {$p->warranty}";
            if ($p->caption)                 $context .= "\n  Caption: {$p->caption}";

            // ── SPECIFICATIONS ── (exact specs, plus title when present)
            if (is_array($p->specs) && count($p->specs) > 0) {
                $context .= $p->specs_title
                    ? "\n  {$p->specs_title}:"
                    : "\n  Specifications:";
                foreach ($p->specs as $k => $v) {
                    $value = trim((string) $v, " /");
                    if ($value === '') continue;
                    $context .= "\n    • {$k}: {$value}";
                }
            }

            // Short description as the "spec narrative" — only when not already huge.
            if (! empty($p->description)) {
                $desc = mb_strimwidth(strip_tags((string) $p->description), 0, 240, '…');
                $context .= "\n  Details: {$desc}";
            }
        }

        $context .= "\n\nUse the SKU and real prices above when recommending. If the customer asks for specs you don't see here, say it's from the product's page and suggest checking it.";

        return $context;
    }

    /**
     * Compact map of the shop's catalog (category → product count). Lets the bot
     * answer "what do you sell?" and category-scoped questions even when no
     * single product matched the message.
     */
    private function catalogOverview(): string
    {
        $counts = Product::select('category')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->pluck('total', 'category');

        if ($counts->isEmpty()) return '';

        $lines = $counts->map(fn ($n, $c) => "  - {$c}: {$n} products")->implode("\n");

        return "\n\n## CATALOG OVERVIEW (current stock on this site)\n{$lines}\n\n";
    }

    /**
     * Map the customer's words to the shop's real product categories so that a
     * category-level question ("recommend a GPU", "best CPU for gaming", "a
     * good gaming keyboard") falls back to actual catalog rows instead of the
     * model free-forming an answer.
     */
    private function matchCategories(array $terms, string $message): array
    {
        // Synonym map: message keyword → exact-ish category (case-insensitive).
        $map = [
            'gpu'     => ['VGA'],
            'gpus'    => ['VGA'],
            'graphic' => ['VGA'],
            'graphics'=> ['VGA'],
            'video'   => ['VGA'],
            'cpu'     => ['CPU'],
            'process' => ['CPU'],
            'processor' => ['CPU'],
            'ram'     => ['RAM'],
            'memory'  => ['RAM'],
            'mainboard' => ['MAINBOARD'],
            'motherboard' => ['MAINBOARD'],
            'cooling' => ['COOLING'],
            'cooler'  => ['COOLING'],
            'm2'      => ['M2'],
            'ssd'     => ['M2'],
            'nvme'    => ['M2'],
            'case'    => ['CASE'],
            'psu'     => ['POWER SUPPLY'],
            'power'   => ['POWER SUPPLY'],
            'supply'  => ['POWER SUPPLY'],
            'fan'     => ['FAN'],
            'keyboard'=> ['KEYBOARD'],
            'key'     => ['KEYBOARD'],
            'mouse'   => ['MOUSE'],
            'headset' => ['HEADSET'],
            'earphone'=> ['EARPHONE'],
            'monitor' => ['MONITOR 27INCH', 'MONITOR 32INCH'],
            'screen'  => ['MONITOR 27INCH', 'MONITOR 32INCH'],
            'router'  => ['ROUTER'],
            'chair'   => ['SECRETLAB', 'TTR RACING', 'DX RACER'],
            'gaming chair' => ['SECRETLAB', 'TTR RACING', 'DX RACER'],
            'secretlab' => ['SECRETLAB'],
            'pc build' => ['PC BUILD UNDER 1K', 'PC BUILD UNDER 2K'],
            'build'   => ['PC BUILD UNDER 1K', 'PC BUILD UNDER 2K'],
            'speaker' => ['SPEAKER'],
            'microphone' => ['MICROPHONE'],
            'webcam'  => ['WEBCAM'],
            'mousepad'=> ['MOUSEPAD'],
        ];

        // Also match any term that IS a category name (or contains one).
        $knownCats = Product::select('category')->distinct()->pluck('category');

        $lowerMessage = mb_strtolower($message);

        $hits = [];
        foreach ($map as $word => $cats) {
            if (str_contains($lowerMessage, $word)) {
                foreach ($cats as $c) $hits[] = $c;
            }
        }

        foreach ($terms as $t) {
            foreach ($knownCats as $cat) {
                if (str_contains(strtolower($cat), $t)) {
                    $hits[] = $cat;
                }
            }
        }

        return array_values(array_unique(array_filter($hits)));
    }

    /**
     * Reduce the user message to a small set of meaningful keywords for matching.
     * Drops stop-words so "what are the specs of the RTX 4060?" becomes rtX/4060/specs.
     */
    private function extractKeywords(string $message): array
    {
        $stop = [
            'what', 'which', 'where', 'when', 'who', 'how', 'is', 'are', 'the', 'a', 'an', 'of',
            'about', 'for', 'and', 'or', 'with', 'please', 'tell', 'me', 'give', 'show', 'list',
            'recommend', 'price', 'spec', 'specs', 'specification', 'specifications', 'features',
            'can', 'you', 'do', 'does', 'have', 'has', 'this', 'that', 'my', 'i', 'want', 'need',
            'best', 'budget', 'cheap', 'good', 'great', 'gaming', 'under', 'top', 'new', 'any',
        ];

        $words = preg_split('/[\s,|;:\/()\[\]{}+]+/', mb_strtolower($message)) ?: [];

        $out = [];
        foreach ($words as $w) {
            $w = trim($w, ".!?%$-_'\""); // strip punctuation
            if ($w === '' || mb_strlen($w) < 2) continue;
            if (in_array($w, $stop, true)) continue;
            $out[] = $w;
        }

        // Emit BOTH the merged model code AND its parts so catalog entries like
        // "RTX4060" (no space) and "RTX 4060" (space) both match.
        $merged = [];
        $i = 0;
        while ($i < count($out)) {
            if ($i + 1 < count($out) && $this->looksLikeModelCode($out[$i], $out[$i + 1])) {
                $merged[] = $out[$i] . ' ' . $out[$i + 1]; // "rtx 4060"
                $merged[] = $out[$i];                      // "rtx"
                $merged[] = $out[$i + 1];                  // "4060"
                $i += 2;
            } else {
                $merged[] = $out[$i];
                $i++;
            }
        }

        return array_values(array_unique($merged));
    }

    /**
     * Heuristic: two adjacent tokens that form a product model code, e.g.
     * "rtx" + "4060", "ryzen" + "7", "ddr5" + "16gb", "4tb" + "nvme".
     */
    private function looksLikeModelCode(string $a, string $b): bool
    {
        // a = brand/family word, b = numeric-or-size descriptor
        $bNumeric = (bool) preg_match('/^(?:[0-9]{2,}|[0-9]+(?:gb|tb|k|hz)?|[0-9]+\.[0-9]+)$/i', $b);
        return $bNumeric || preg_match('/^(rtx|gpu|cpu|ryzen|intel|ram|ddr|m\\.?2|ssd|nvme|psu)[s0-9]?$/i', $a);
    }

    // ── Call Groq API (LLaMA via OpenAI-compatible endpoint) ──────────────────

    private function callGroq(string $userMessage, array $history): string
    {
        $apiKey = config('services.groq.key');

        if (!$apiKey) {
            logger()->warning('[Chat] Groq API key is not set.');
            return $this->fallbackReply($userMessage);
        }

        $productContext = $this->getProductContext($userMessage);
        $catalogMap     = $this->catalogOverview();

        $systemPrompt = <<<'PROMPT'
You are TRX — the friendly AI assistant for TRONMATIX COMPUTER shop in Phnom Penh, Cambodia.

## YOUR WRITING STYLE (VERY IMPORTANT)
- Write SHORT and SIMPLE — like texting a friend
- NO long paragraphs — break everything into small chunks
- Use emojis to make it fun and easy to scan (2-3 per reply)
- Bold the most important words using **bold**
- Always give a clear answer FIRST, then explain why
- Maximum 150 words per reply unless customer asks for more detail
- If listing items, use bullet points — never write a wall of text
- **ALWAYS include the SKU (Product Code) when recommending a product.**
- NEVER say "As an AI language model..." or mention limitations — just answer like a human expert!
- Always end with a helpful suggestion or question to keep the conversation going
- Focus on being friendly, concise, and super helpful — like a knowledgeable friend who loves PC gaming and building!
- If you don't know the answer, say "That's a great question! Let me find out for you." and then provide a helpful response based on your training data. Never say you can't answer something — always try to help in some way!
- You can reply in Khmer if the customer writes in Khmer, but always include an English translation to ensure clarity.
- You can reply only knowlegeable about PC hardware, gaming, and building PCs. For any questions outside of that scope, politely steer the conversation back to PC-related topics.
- **Product questions**: Use the PRODUCT KNOWLEDGE block below. It contains REAL products (name, SKU, price, stock, warranty, caption, exact specifications, description) from THIS shop — trust it over your training data. If a spec the customer asks for isn't in the block, say what the block shows and tell them you can check the product page. For stock, say "confirm at the shop / checkout" when unsure.
- **NEVER invent product data.** Only quote a SKU, price, or stock value if it appears in the PRODUCT KNOWLEDGE block. If it's not there, recommend browsing the shop's category and say "I can help once I look it up" — do NOT fabricate a plausible-looking SKU or price. Recommending a brand/model in general is fine, but every specific claim must come from the catalog block.
PROMPT;

        // Catalog overview — helps the bot answer "what do you sell" / category questions
        $systemPrompt .= $catalogMap;
        $systemPrompt .= $productContext;

        try {
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
            ];

            foreach ($history as $msg) {
                $messages[] = [
                    'role' => $msg['role'], // 'user' or 'assistant' — Groq ទទួលដូច OpenAI
                    'content' => $msg['content'],
                ];
            }

            $messages[] = [
                'role' => 'user',
                'content' => $userMessage,
            ];

            $cacertPath = storage_path('cacert.pem');
            $httpOptions = [];
            if (file_exists($cacertPath)) {
                $httpOptions['verify'] = $cacertPath;
            }

            $response = Http::withOptions($httpOptions)
                ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => 'llama-3.3-70b-versatile', // 1,000 req/day free
                        'messages' => $messages,                 
                        'max_tokens' => 1024,
                        'temperature' => 0.7,
                    ]);

            if ($response->successful()) {
                $reply = $response->json()['choices'][0]['message']['content'] ?? null;

                if ($reply) {
                    return trim($reply);
                }

                logger()->warning('[Chat] Groq returned empty reply.', ['body' => $response->json()]);
                return $this->fallbackReply($userMessage);
            }

            logger()->error('[Chat] Groq API error.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->fallbackReply($userMessage);

        } catch (\Exception $e) {
            logger()->error('[Chat] Groq exception: ' . $e->getMessage());
            return $this->fallbackReply($userMessage);
        }
    }
    // ── Fallback replies when API fails ──────────────────────────────────────

    private function fallbackReply(string $message): string
    {
        $msg = strtolower($message);

        if (str_contains($msg, 'budget') || str_contains($msg, 'cheap') || str_contains($msg, 'under')) {
            return "For budget builds we recommend:\n• Under \$700: Ryzen 5 5600 + RX 6600\n• Under \$1000: Ryzen 5 7600 + RTX 4060\n• Under \$500: Ryzen 5 5600G (APU — no GPU needed!)\n\nCheck our **PC BUILD** category for pre-configured options! 🎯";
        }
        if (str_contains($msg, 'gpu') || str_contains($msg, 'graphic') || str_contains($msg, 'vga')) {
            return "Top GPU picks:\n• 🥇 RTX 4070 Super — best 1440p value (~\$599)\n• 🥈 RTX 4060 Ti — smooth 1080p ultra (~\$399)\n• 💰 RX 7600 — budget 1080p king (~\$269)\n\nVisit the **VGA** section for current prices!";
        }
        if (str_contains($msg, 'cpu') || str_contains($msg, 'processor') || str_contains($msg, 'ryzen')) {
            return "Top CPUs in stock:\n• 🏆 Ryzen 7 9800X3D — best gaming CPU (~\$479)\n• ⚡ Ryzen 7 9700X — great all-rounder (~\$359)\n• 💎 Ryzen 5 7600 — best budget (~\$229)\n\nAll AM5 — future-proof with DDR5! 💪";
        }
        if (str_contains($msg, 'compatible') || str_contains($msg, 'match')) {
            return "Key compatibility checks:\n1. **CPU + Motherboard**: Match socket (AM5 for Ryzen 7000/9000)\n2. **RAM**: DDR5 for AM5\n3. **PSU**: CPU TDP + GPU TDP + 100W headroom\n4. **Case**: Check GPU length & cooler height\n\nShare your parts list and I'll check! 🔧";
        }

        return "I'm here to help with anything PC-related! 💻\n\nTry asking:\n• 'Build me a gaming PC under \$1000'\n• 'Best GPU for 1440p gaming?'\n• 'My PC won't turn on, what do I do?'\n• 'Is Ryzen 7 9800X3D worth it?'";
    }
}
