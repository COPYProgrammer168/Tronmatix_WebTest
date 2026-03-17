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
        $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|integer|exists:chat_sessions,id',
            'history' => 'nullable|array',
            'history.*.role' => 'required_with:history|in:user,assistant',
            'history.*.content' => 'required_with:history|string',
        ]);

        $user = Auth::user();

        // FIX [2]: rate-limit per user (10 msg/min) or per IP for guests (5 msg/min)
        $rateLimitKey = $user ? 'chat:user:'.$user->id : 'chat:ip:'.$request->ip();
        $rateLimitMaxAttempts = $user ? 10 : 5;

        if (RateLimiter::tooManyAttempts($rateLimitKey, $rateLimitMaxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return response()->json([
                'success' => false,
                'message' => "Too many messages. Please wait {$seconds} seconds.",
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 60); // 1-minute window

        // ── Get or create session ─────────────────────────────────────────────
        if ($request->session_id) {
            $session = ChatSession::find($request->session_id);

            // FIX [3]: verify session ownership — prevent injecting into another user's session
            if ($session && $user && $session->user_id !== null && $session->user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
        } else {
            $session = ChatSession::create(['user_id' => $user?->id, 'status' => 'open']);
        }

        if (! $session) {
            $session = ChatSession::create(['user_id' => $user?->id, 'status' => 'open']);
        }

        // Persist user message
        ChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'user',
            'message' => $request->message,
        ]);

        // Call AI
        $reply = $this->callAi($request->message, $request->history ?? []);

        // Persist bot reply
        ChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'bot',
            'message' => $reply,
        ]);

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'session_id' => $session->id,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function callAi(string $userMessage, array $history): string
    {
        $apiKey = config('services.openai.key');
        if (! $apiKey) {
            return $this->fallbackReply($userMessage);
        }

        $systemPrompt = <<<'PROMPT'
You are a knowledgeable PC hardware expert assistant for TRONMATIX COMPUTER, a Cambodian computer hardware shop.

Your role:
- Help customers choose CPU, GPU, RAM, Motherboard, SSD, PSU, and Case
- Suggest complete PC builds within a budget
- Check component compatibility (CPU socket, RAM type, PCIe slots, TDP)
- Recommend products available in our shop
- Answer technical questions about overclocking, cooling, and benchmarks

Shop specialties: AMD Ryzen processors, NVIDIA RTX GPUs, gaming PCs, budget builds.
Tone: Friendly, professional, concise. Use KHR/USD pricing context for Cambodia.
Always suggest checking product pages for current pricing.
PROMPT;

        $messages = array_merge(
            array_map(fn ($h) => ['role' => $h['role'], 'content' => $h['content']], $history),
            [['role' => 'user', 'content' => $userMessage]]
        );

        try {
            // FIX [1]: withoutVerifying() only on local/dev — never in production
            $http = Http::withToken($apiKey)->timeout(20);
            if (app()->environment('local', 'development')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => array_merge(
                    [['role' => 'system', 'content' => $systemPrompt]],
                    $messages
                ),
                'max_tokens' => 512,
                'temperature' => 0.7,
            ]);

            return $response->json('choices.0.message.content')
                ?? 'Sorry, I could not generate a response. Please try again.';

        } catch (\Throwable $e) {
            logger()->error('AI chat error: '.$e->getMessage());

            return $this->fallbackReply($userMessage);
        }
    }

    private function fallbackReply(string $message): string
    {
        $msg = strtolower($message);

        if (str_contains($msg, 'budget') || str_contains($msg, 'cheap') || str_contains($msg, '$')) {
            return "For budget builds, we recommend:\n• Under \$1000: AMD Ryzen 5 + RX 6600 combo\n• Under \$500: Ryzen 5 5600G (APU, no GPU needed)\n\nCheck our **PC BUILD** category for pre-configured packages! 🎯";
        }
        if (str_contains($msg, 'gpu') || str_contains($msg, 'graphic') || str_contains($msg, 'vga')) {
            return "Our top GPU picks:\n• 🥇 RTX 4070 Super — best value 1440p gaming\n• 🥈 RTX 4060 Ti — smooth 1080p ultra\n• 💰 RX 6700 XT — budget 1440p king\n\nVisit the **VGA** section for current prices!";
        }
        if (str_contains($msg, 'cpu') || str_contains($msg, 'processor') || str_contains($msg, 'ryzen')) {
            return "Top CPUs in stock:\n• AMD Ryzen 7 9800X3D — \$349 (best gaming CPU!)\n• AMD Ryzen 7 9700X — \$299 (great all-rounder)\n• AMD Ryzen 9 9950X3D — \$589 (workstation king)\n\nAll are AM5 socket for future-proofing. 💪";
        }
        if (str_contains($msg, 'compatible') || str_contains($msg, 'match')) {
            return "For compatibility, key things to check:\n1. **CPU & Motherboard**: Socket (AM5 for Ryzen 7000+)\n2. **RAM**: DDR5 for AM5, DDR4 for older builds\n3. **PSU wattage**: Add CPU TDP + GPU TDP + 20%\n4. **Case clearance**: GPU length & cooler height\n\nTell me your parts and I'll check for you! 🔧";
        }

        return "I'm here to help with PC builds, part selection, and compatibility checks! Try asking:\n• 'What GPU should I get for gaming?'\n• 'Build me a PC under \$800'\n• 'Is Ryzen 7 9800X3D compatible with B650 motherboard?'";
    }
}
