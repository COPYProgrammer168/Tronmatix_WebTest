<?php

// app/Services/TelegramBotService.php
//
// FIX: Switched from MarkdownV2 to HTML parse_mode.
// MarkdownV2 requires escaping of ALL special characters: _ * [ ] ( ) ~ ` > # + - = | { } . ! \
// Even ONE unescaped character causes Telegram to silently reject the message.
// HTML mode is much more forgiving — only <, >, & need escaping.
// We use htmlspecialchars() for user data and <b>, <i>, <code> for formatting.

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    private string $token;
    private string $ownerChatId;
    private string $apiBase;
    private string $miniAppUrl;
    private string $webhookSecret;

    public function __construct()
    {
        $this->token         = config('services.telegram_user.bot_token', '');
        $this->ownerChatId   = config('services.telegram_user.chat_id', '');
        $this->webhookSecret = config('services.telegram_user.webhook_secret', '');
        $this->miniAppUrl    = config('services.telegram_user.mini_app_url', '');
        $this->apiBase       = "https://api.telegram.org/bot{$this->token}";
    }

    // =========================================================================
    //  INBOUND — WEBHOOK DISPATCHER
    // =========================================================================

    public function handleUpdate(array $update): void
    {
        if (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
            return;
        }

        $message = $update['message'] ?? null;
        if (! $message || ! isset($message['text'])) return;

        $chatId = (string) $message['chat']['id'];
        $text   = trim($message['text']);
        $from   = $message['from'] ?? [];

        Log::info('[UserBot] incoming', ['chat_id' => $chatId, 'text' => $text]);

        match (true) {
            str_starts_with($text, '/start')      => $this->cmdStart($chatId, $from),
            str_starts_with($text, '/orders')     => $this->cmdOrders($chatId),
            str_starts_with($text, '/status')     => $this->cmdStatus($chatId, $text),
            str_starts_with($text, '/profile')    => $this->cmdProfile($chatId),
            str_starts_with($text, '/disconnect') => $this->cmdDisconnect($chatId),
            str_starts_with($text, '/help')       => $this->cmdHelp($chatId),
            default                               => $this->cmdUnknown($chatId),
        };
    }

    public function verifyWebhookSecret(string $headerValue): bool
    {
        if (! $this->webhookSecret) return true;
        return hash_equals($this->webhookSecret, $headerValue);
    }

    // =========================================================================
    //  COMMANDS — all use HTML parse mode now
    // =========================================================================

    private function cmdStart(string $chatId, array $from): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if ($user) {
            $uname = $this->e($user->username ?? 'there');
            $text  = "👋 <b>Welcome back, {$uname}!</b>\n\n"
                   . "Your account is connected ✅\n"
                   . "You'll receive order notifications right here.\n\n"
                   . "Tap a button below or type /help";
        } else {
            $fname = $this->e($from['first_name'] ?? 'there');
            $text  = "👋 <b>Hello, {$fname}!</b>\n\n"
                   . "Welcome to our notification bot.\n\n"
                   . "To get order updates here:\n"
                   . "1. Log in to the app\n"
                   . "2. Go to Profile → <b>Connect with Telegram</b>\n\n"
                   . "📱 Or open the app directly below.";
        }

        $this->send($chatId, $text, $this->mainKeyboard());
    }

    private function cmdOrders(string $chatId): void
    {
        $user = $this->resolveUser($chatId);
        if (! $user) return;

        $orders = Order::where('user_id', $user->id)
            ->with('items')->latest()->take(5)->get();

        if ($orders->isEmpty()) {
            $this->send($chatId, "📦 You have no orders yet!\n\nOpen the app to start shopping!", $this->mainKeyboard());
            return;
        }

        $lines = ["📋 <b>Your Recent Orders</b>\n"];
        foreach ($orders as $order) {
            $emoji  = $this->statusEmoji($order->status);
            $id     = $this->e($order->order_id ?? (string) $order->id);
            $total  = $this->e((string) $order->total);
            $status = $this->e(ucfirst($order->status));
            $date   = $this->e($order->created_at->format('d M Y'));

            $lines[] = "{$emoji} <code>#{$id}</code> — \${$total}";
            $lines[] = "   Status: <b>{$status}</b>";
            $lines[] = "   Date: {$date}";
            $lines[] = '';
        }
        $lines[] = 'Type <code>/status ORDER_ID</code> to track an order.';

        $this->send($chatId, implode("\n", $lines), $this->mainKeyboard());
    }

    private function cmdStatus(string $chatId, string $text): void
    {
        $user = $this->resolveUser($chatId);
        if (! $user) return;

        $parts   = explode(' ', $text, 2);
        $orderId = trim($parts[1] ?? '');

        if (! $orderId) {
            $this->send($chatId, "ℹ️ Usage: <code>/status ORDER_ID</code>\n\nExample: <code>/status ORD-2026-001</code>");
            return;
        }

        $order = Order::where('user_id', $user->id)
            ->where('order_id', $orderId)
            ->with('items')->first();

        if (! $order) {
            $safe = $this->e($orderId);
            $this->send($chatId, "❌ Order <code>{$safe}</code> not found or doesn't belong to your account.");
            return;
        }

        $emoji     = $this->statusEmoji($order->status);
        $id        = $this->e($order->order_id ?? (string) $order->id);
        $total     = $this->e((string) $order->total);
        $method    = $this->e(strtoupper($order->payment_method ?? ''));
        $placed    = $this->e($order->created_at->format('d M Y, H:i'));
        $status    = $this->e(ucfirst($order->status));
        $itemLines = $order->items->map(fn($i) => '  • '.$this->e($i->name).'  ×'.$i->qty)->join("\n");

        $lines = [
            "{$emoji} <b>Order #{$id}</b>", '',
            "📅 Placed: {$placed}",
            "📊 Status: <b>{$status}</b>", '',
            '<b>Items:</b>',
            $itemLines, '',
            "💰 Total: <b>\${$total}</b>",
            "💳 Payment: {$method}",
        ];

        if ($order->delivery_date) {
            $slot    = $order->delivery_time_slot ? ' | '.$this->e($order->delivery_time_slot) : '';
            $lines[] = '🗓 Delivery: '.$this->e($order->delivery_date).$slot;
        }

        $this->send($chatId, implode("\n", $lines), $this->mainKeyboard());
    }

    private function cmdProfile(string $chatId): void
    {
        $user = $this->resolveUser($chatId);
        if (! $user) return;

        $role  = $this->e(ucfirst($user->role ?? 'customer'));
        $since = $this->e($user->created_at->format('d M Y'));
        $uname = $this->e($user->username ?? 'N/A');
        $email = $this->e($user->email ?? '');

        $spent      = '0.00';
        try { $spent = $this->e(number_format((float) $user->totalSpent(), 2)); } catch (\Throwable) {}
        $orderCount = Order::where('user_id', $user->id)->count();

        $this->send($chatId, implode("\n", [
            '👤 <b>Your Account</b>', '',
            "🆔 Username: <b>{$uname}</b>",
            "📧 Email: <code>{$email}</code>",
            "⭐ Role: {$role}",
            "📦 Orders: {$orderCount}",
            "💰 Total spent: \${$spent}",
            '📱 Telegram: ✅ Connected',
            "🕐 Member since: {$since}",
        ]), $this->mainKeyboard());
    }

    private function cmdHelp(string $chatId): void
    {
        $this->send($chatId, implode("\n", [
            '🤖 <b>Available Commands</b>', '',
            '/start — Welcome message',
            '/orders — Your recent orders',
            '/status ORDER_ID — Track a specific order',
            '/profile — Your account info',
            '/disconnect — Unlink this Telegram account',
            '/help — Show this message',
            '',
            '🔔 <b>Auto-notifications:</b>',
            '• Order placed → receipt sent',
            '• Order confirmed',
            '• Order shipped',
            '• Order delivered',
            '• Order cancelled',
        ]), $this->mainKeyboard());
    }

    private function cmdDisconnect(string $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (! $user) {
            $this->send($chatId, "❌ No account is linked to this Telegram.\n\nNothing to disconnect.", $this->mainKeyboard());
            return;
        }

        $uname = $this->e($user->username ?? 'your account');
        $user->update([
            'telegram_chat_id'      => null,
            'telegram_username'     => null,
            'telegram_connected_at' => null,
        ]);

        $this->send($chatId, implode("\n", [
            '🔓 <b>Account Disconnected</b>', '',
            "Your Telegram has been unlinked from <b>{$uname}</b>.", '',
            "You won't receive order notifications anymore.", '',
            'To reconnect, visit your profile page on the website and click <b>Connect with Telegram</b>.',
        ]));
    }

    private function cmdUnknown(string $chatId): void
    {
        $this->send($chatId, "🤔 I didn't understand that.\n\nType /help to see available commands.", $this->mainKeyboard());
    }

    private function handleCallback(array $callback): void
    {
        $chatId = (string) $callback['message']['chat']['id'];
        $data   = $callback['data'] ?? '';

        match ($data) {
            'orders'  => $this->cmdOrders($chatId),
            'profile' => $this->cmdProfile($chatId),
            'help'    => $this->cmdHelp($chatId),
            default   => null,
        };

        Http::timeout(5)->withoutVerifying()
            ->post("{$this->apiBase}/answerCallbackQuery", [
                'callback_query_id' => $callback['id'],
            ]);
    }

    // =========================================================================
    //  OUTBOUND — ORDER EVENT NOTIFICATIONS
    // =========================================================================

    public function onOrderPlaced(Order $order): void
    {
        if ($chatId = $order->user?->telegram_chat_id) {
            $this->send($chatId, $this->buildReceiptMessage($order), $this->mainKeyboard());
        }
    }

    public function onOrderConfirmed(Order $order): void
    {
        if (! $chatId = $order->user?->telegram_chat_id) return;
        $id    = $this->e($order->order_id ?? (string) $order->id);
        $total = $this->e((string) $order->total);

        $this->send($chatId, implode("\n", [
            '✅ <b>Your order has been confirmed!</b>', '',
            "📦 Order: <code>#{$id}</code>",
            "💰 Total: \${$total}", '',
            "We're preparing your items.",
            '🕐 '.$this->ts(),
        ]), $this->mainKeyboard());
    }

    public function onOrderProcessing(Order $order): void
    {
        if (! $chatId = $order->user?->telegram_chat_id) return;
        $id    = $this->e($order->order_id ?? (string) $order->id);
        $items = $this->e($this->itemSummaryLine($order));
        $total = $this->e((string) $order->total);

        $lines = ['⚙️ <b>Your order is being prepared!</b>', '',
            "📦 Order: <code>#{$id}</code>", "🛍️ {$items}", "💰 Total: \${$total}"];

        if ($order->delivery_date) {
            $slot    = $order->delivery_time_slot ? ' | '.$this->e($order->delivery_time_slot) : '';
            $lines[] = '🗓 Estimated delivery: '.$this->e($order->delivery_date).$slot;
        }
        $lines[] = '';
        $lines[] = '🔧 Our team is packing your items right now.';
        $lines[] = "🚚 You'll receive a shipping update soon.";
        $lines[] = '🕐 '.$this->ts();

        $this->send($chatId, implode("\n", $lines), $this->mainKeyboard());
    }

    public function onOrderShipped(Order $order): void
    {
        if (! $chatId = $order->user?->telegram_chat_id) return;
        $id    = $this->e($order->order_id ?? (string) $order->id);
        $total = $this->e((string) $order->total);

        $lines = ['🚚 <b>Your order is on its way!</b>', '',
            "📦 Order: <code>#{$id}</code>", "💰 Total: \${$total}"];

        if ($order->delivery_date) {
            $slot    = $order->delivery_time_slot ? ' | '.$this->e($order->delivery_time_slot) : '';
            $lines[] = '🗓 Expected: '.$this->e($order->delivery_date).$slot;
        }
        $lines[] = '';
        $lines[] = '📍 Please be ready to receive your order!';
        $lines[] = '🕐 '.$this->ts();

        $this->send($chatId, implode("\n", $lines), $this->mainKeyboard());
    }

    public function onOrderDelivered(Order $order): void
    {
        if (! $chatId = $order->user?->telegram_chat_id) return;
        $id    = $this->e($order->order_id ?? (string) $order->id);
        $total = $this->e((string) $order->total);

        $this->send($chatId, implode("\n", [
            '🎉 <b>Order Delivered!</b>', '',
            "📦 Order <code>#{$id}</code> has been delivered.",
            "💰 Total paid: \${$total}", '',
            '💙 Thank you for shopping with us!',
            '🕐 '.$this->ts(),
        ]), $this->mainKeyboard());
    }

    public function onOrderCancelled(Order $order): void
    {
        if (! $chatId = $order->user?->telegram_chat_id) return;
        $id    = $this->e($order->order_id ?? (string) $order->id);
        $total = $this->e((string) $order->total);

        $this->send($chatId, implode("\n", [
            '🚫 <b>Order Cancelled</b>', '',
            "📦 Order <code>#{$id}</code> has been cancelled.",
            "💰 Amount: \${$total}", '',
            'If you have questions, please contact our support.',
            '🕐 '.$this->ts(),
        ]), $this->mainKeyboard());
    }

    // =========================================================================
    //  BOT SETUP
    // =========================================================================

    public function registerWebhook(string $webhookUrl): array
    {
        return Http::timeout(15)->withoutVerifying()->asJson()
            ->post("{$this->apiBase}/setWebhook", [
                'url'                  => $webhookUrl,
                'allowed_updates'      => ['message', 'callback_query'],
                'secret_token'         => $this->webhookSecret ?: null,
                'drop_pending_updates' => true,
            ])->json();
    }

    public function getWebhookInfo(): array
    {
        return Http::timeout(10)->withoutVerifying()->get("{$this->apiBase}/getWebhookInfo")->json();
    }

    public function deleteWebhook(): array
    {
        return Http::timeout(10)->withoutVerifying()->asJson()
            ->post("{$this->apiBase}/deleteWebhook", ['drop_pending_updates' => true])->json();
    }

    public function setBotCommands(): array
    {
        return Http::timeout(10)->withoutVerifying()->asJson()
            ->post("{$this->apiBase}/setMyCommands", [
                'commands' => [
                    ['command' => 'start',      'description' => 'Welcome message & open app'],
                    ['command' => 'orders',     'description' => 'View your recent orders'],
                    ['command' => 'status',     'description' => 'Track a specific order'],
                    ['command' => 'profile',    'description' => 'View your account info'],
                    ['command' => 'disconnect', 'description' => 'Unlink your Telegram from this account'],
                    ['command' => 'help',       'description' => 'Show all commands'],
                ],
            ])->json();
    }

    public function sendTestMessage(User $user): bool
    {
        if (! $user->telegram_chat_id) return false;
        $uname = $this->e($user->telegram_username ?? 'unknown');

        return $this->send($user->telegram_chat_id, implode("\n", [
            '👋 <b>Test Message</b>', '',
            'Your Telegram notifications are working correctly!',
            "🆔 Connected as: @{$uname}",
            '🕐 '.$this->ts(),
        ]));
    }

    // =========================================================================
    //  PRIVATE HELPERS
    // =========================================================================

    private function resolveUser(string $chatId): ?User
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        if (! $user) {
            $this->send(
                $chatId,
                "❌ No account linked to this Telegram.\n\n"
                ."Open the app → Profile → <b>Connect with Telegram</b>.",
                $this->mainKeyboard()
            );
        }
        return $user;
    }

    private function buildReceiptMessage(Order $order): string
    {
        $shipping = $order->shipping;
        if (is_string($shipping)) $shipping = json_decode($shipping, true) ?? [];

        $id       = $this->e($order->order_id ?? (string) $order->id);
        $address  = $this->e(($shipping['address'] ?? '—').(isset($shipping['city']) && $shipping['city'] ? ', '.$shipping['city'] : ''));
        $method   = $this->e(strtoupper($order->payment_method ?? ''));
        $subtotal = $this->e((string) ($order->subtotal ?? $order->total));
        $total    = $this->e((string) $order->total);

        $itemLines = $order->items->map(function ($item) {
            $lineTotal = round($item->price * $item->qty, 2);
            $name      = $this->e($item->name);
            return "  • {$name} ×{$item->qty} → \${$lineTotal}";
        })->join("\n");

        $lines = array_filter([
            '🧾 <b>Your Order Receipt</b>', '',
            "📦 Order: <code>#{$id}</code>",
            "📍 Deliver to: {$address}",
            $order->delivery_date
                ? '🗓 Delivery: '.$this->e($order->delivery_date)
                    .($order->delivery_time_slot ? ' | '.$this->e($order->delivery_time_slot) : '')
                : null,
            "💳 Payment: {$method}", '',
            '<b>Items:</b>',
            $itemLines, '',
            "💰 Subtotal: \${$subtotal}",
            ($order->discount_amount ?? 0) > 0
                ? '🏷 Discount ('.$this->e($order->discount_code ?? '').'-): -$'.$this->e((string) $order->discount_amount)
                : null,
            "✅ <b>Total: \${$total}</b>", '',
            "We'll notify you when your order ships. 💙",
            '🕐 '.$this->ts(),
        ], fn($l) => $l !== null);

        return implode("\n", $lines);
    }

    private function mainKeyboard(): array
    {
        $kb = [
            'inline_keyboard' => [
                [
                    ['text' => '📦 My Orders', 'callback_data' => 'orders'],
                    ['text' => '👤 Profile',   'callback_data' => 'profile'],
                ],
                [
                    ['text' => '❓ Help', 'callback_data' => 'help'],
                ],
            ],
        ];

        if ($this->miniAppUrl) {
            $kb['inline_keyboard'][] = [
                ['text' => '📱 Open App', 'web_app' => ['url' => $this->miniAppUrl]],
            ];
        }

        return $kb;
    }

    /**
     * FIX: Switched to HTML parse_mode.
     * MarkdownV2 crashes on ANY unescaped special char (-, ., !, @, etc.)
     * HTML only needs &, <, > escaped via htmlspecialchars().
     */
    private function send(string $chatId, string $text, ?array $replyMarkup = null): bool
    {
        if (! $this->token) {
            Log::warning('[UserBot] TELEGRAM_USER_BOT_TOKEN not set.');
            return false;
        }

        $payload = [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',   // FIX: was MarkdownV2 — too fragile
        ];
        if ($replyMarkup) $payload['reply_markup'] = $replyMarkup;

        try {
            $res = Http::timeout(8)->withoutVerifying()->asJson()
                ->post("{$this->apiBase}/sendMessage", $payload);

            if (! $res->successful()) {
                Log::error('[UserBot] sendMessage failed', [
                    'chat_id' => $chatId,
                    'status'  => $res->status(),
                    'body'    => $res->body(),
                    'text_preview' => substr($text, 0, 200),
                ]);
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            Log::warning('[UserBot] exception: '.$e->getMessage());
            return false;
        }
    }

    private function itemSummaryLine(Order $order): string
    {
        $count = $order->items->sum('qty');
        $names = $order->items->pluck('name')->take(3)->join(', ');
        $more  = $order->items->count() > 3 ? '...' : '';
        return "{$count} item(s): {$names}{$more}";
    }

    /**
     * FIX: Use htmlspecialchars() instead of regex escape.
     * HTML mode only needs: & → &amp;  < → &lt;  > → &gt;
     */
    private function e(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function statusEmoji(string $status): string
    {
        return match ($status) {
            'pending'    => '⏳',
            'confirmed'  => '✅',
            'processing' => '⚙️',
            'shipped'    => '🚚',
            'delivered'  => '🎉',
            'cancelled'  => '🚫',
            default      => '📦',
        };
    }

    private function ts(): string
    {
        return now()->format('d M Y, H:i');
    }
}
