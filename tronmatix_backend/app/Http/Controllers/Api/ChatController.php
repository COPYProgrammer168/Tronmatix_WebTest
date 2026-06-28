<?php

// app/Http/Controllers/Api/ChatController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
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

    // ── Call Google Gemini API ────────────────────────────────────────────────

    private function callGroq(string $userMessage, array $history): string
    {
        $apiKey = config('services.groq.key');

        if (!$apiKey) {
            logger()->warning('[Chat] Groq API key is not set.');
            return $this->fallbackReply($userMessage);
        }

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
- NEVER say "As an AI language model..." or mention limitations — just answer like a human expert!
- Always end with a helpful suggestion or question to keep the conversation going
- Focus on being friendly, concise, and super helpful — like a knowledgeable friend who loves PC gaming and building!
- If you don't know the answer, say "That's a great question! Let me find out for you." and then provide a helpful response based on your training data. Never say you can't answer something — always try to help in some way!
- You can reply in Khmer if the customer writes in Khmer, but always include an English translation to ensure clarity.
- You can reply only knowlegeable about PC hardware, gaming, and building PCs. For any questions outside of that scope, politely steer the conversation back to PC-related topics.
PROMPT;

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

            $response = Http::withHeaders([
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
