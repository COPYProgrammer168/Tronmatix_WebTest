<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    private string $token;

    private string $chatId;

    private string $apiBase;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token', '');
        $this->chatId = config('services.telegram.chat_id', '');
        $this->apiBase = "https://api.telegram.org/bot{$this->token}";
    }

    // ── Public API ────────────────────────────────────────────────────────────

    /** Send a new-order receipt to all admin chat IDs. */
    public function sendReceipt(Order $order): void
    {
        if (! $this->token) {
            return;
        }

        $shipping = $order->shipping;
        if (is_string($shipping)) {
            $shipping = json_decode($shipping, true) ?? [];
        }

        $itemLines = $order->items->map(function ($item) {
            $lineTotal = round($item->price * $item->qty, 2);

            return "  • {$item->name} ×{$item->qty}  →  \${$lineTotal}";
        })->join("\n");

        $lines = array_filter([
            '🛒 *New Order Placed!*',
            '',
            "📦 Order: `#{$order->order_id}`",
            '👤 Customer: '.($order->user?->username ?? 'Guest'),
            '📞 Phone: '.($shipping['phone'] ?? '—'),
            '📍 Address: '.($shipping['address'] ?? '—').($shipping['city'] ? ', '.$shipping['city'] : ''),
            $order->delivery_date
                ? "🗓 Delivery: {$order->delivery_date}".($order->delivery_time_slot ? " | {$order->delivery_time_slot}" : '')
                : null,
            '💳 Payment: '.strtoupper($order->payment_method),
            '',
            '*Items:*',
            $itemLines,
            '',
            "💰 Subtotal: \${$order->subtotal}",
            $order->discount_amount > 0
                ? "🏷 Discount ({$order->discount_code}): −\${$order->discount_amount}"
                : null,
            "✅ *Total: \${$order->total}*",
            '',
            '🕐 '.$order->created_at->setTimezone('Asia/Phnom_Penh')->format('d M Y, H:i').' (ICT)',
        ], fn ($line) => $line !== null);

        $this->send(implode("\n", $lines));
    }

    /** Notify when admin confirms delivery. */
    public function sendDeliveryConfirmed(Order $order): void
    {
        if (! $this->token) {
            return;
        }

        $message = implode("\n", [
            '✅ *Delivery Confirmed!*',
            '',
            "📦 Order `#{$order->order_id}` has been delivered.",
            '👤 Customer: '.($order->user?->username ?? 'Guest'),
            '🕐 Confirmed: '.now('Asia/Phnom_Penh')->format('d M Y, H:i').' (ICT)',
        ]);

        $this->send($message);

        if ($order->user?->telegram_chat_id) {
            $this->send($message, $order->user->telegram_chat_id);
        }
    }

    /** Payment confirmed via ABA webhook — notify admin. */
    public function sendPaymentConfirmed(Order $order, string $apv): void
    {
        if (! $this->token) {
            return;
        }

        $message = implode("\n", [
            '💳 *ABA BAKONG Payment Received!*',
            '',
            "📦 Order: `#{$order->order_id}`",
            "💰 Amount: \${$order->total}",
            "🔑 APV: {$apv}",
            '👤 Customer: '.($order->user?->username ?? 'Guest'),
            '🕐 '.now('Asia/Phnom_Penh')->format('d M Y, H:i').' (ICT)',
        ]);

        $this->send($message);
    }

    /** Send a plain text alert (for generic events). */
    public function sendAlert(string $text): void
    {
        if (! $this->token) {
            return;
        }
        $this->send($text);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /** Send a Markdown message to one or all configured chat IDs. */
    private function send(string $text, ?string $chatId = null): void
    {
        $targets = $chatId
            ? [$chatId]
            : array_filter(array_map('trim', explode(',', $this->chatId)));

        foreach ($targets as $target) {
            try {
                $res = Http::timeout(8)
                    ->withoutVerifying()
                    ->post("{$this->apiBase}/sendMessage", [
                        'chat_id' => $target,
                        'text' => $text,
                        'parse_mode' => 'Markdown',
                    ]);

                if (! $res->successful()) {
                    \Illuminate\Support\Facades\Log::error(
                        'Telegram API error: '.$res->status().' — '.$res->body()
                    );
                }
            } catch (\Throwable $e) {
                // Log but don't crash — Telegram failure must never break order flow
                \Illuminate\Support\Facades\Log::warning(
                    "Telegram send failed (chat_id={$target}): ".$e->getMessage()
                );
            }
        }
    }
}
