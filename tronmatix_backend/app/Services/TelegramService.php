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

    /**
     * Send a new-order receipt to all admin chat IDs.
     * Clearly shows PICKUP vs DELIVERY so staff know what to do.
     */
    public function sendReceipt(Order $order): void
    {
        if (!$this->token)
            return;

        $shipping = $order->shipping;
        if (is_string($shipping)) {
            $shipping = json_decode($shipping, true) ?? [];
        }

        $isPickup = $order->isPickup();

        $itemLines = $order->items->map(function ($item) {
            $lineTotal = round($item->price * $item->qty, 2);
            $warranty = '';
            if ($item->warranty_start && $item->warranty_end) {
                $warranty = "\n     🛡 Warranty: " . $item->warranty_start->format('d.m.Y')
                    . ' - ' . $item->warranty_end->format('d.m.Y');
            }
            return "  • {$item->name} ×{$item->qty}  →  \${$lineTotal}{$warranty}";
        })->join("\n");

        if ($isPickup) {
            $fulfillmentLine = '🏪 *STORE PICKUP* — customer will come to collect';
            $contactLine = '👤 Customer: ' . ($order->user?->username ?? 'Guest');
            $phoneLine = '📞 Phone: ' . ($shipping['phone'] ?? '—');
            $addressLine = null;
        } else {
            $fulfillmentLine = '🚚 *DELIVERY*';
            $contactLine = '👤 Customer: ' . ($order->user?->username ?? 'Guest');
            $phoneLine = '📞 Phone: ' . ($shipping['phone'] ?? '—');
            $addressLine = '📍 Address: '
                . ($shipping['address'] ?? '—')
                . ($shipping['city'] ? ', ' . $shipping['city'] : '');
        }

        $scheduleLine = null;
        if ($order->delivery_date) {
            $dateLabel = $isPickup ? '🗓 Preferred Pickup' : '🗓 Delivery';
            $scheduleLine = $dateLabel . ': ' . $order->delivery_date
                . ($order->delivery_time_slot ? ' | ' . $order->delivery_time_slot : '');
        }

        $lines = array_filter([
            '🛒 *New Order Placed!*',
            '',
            "📦 Order: `#{$order->order_id}`",
            $fulfillmentLine,
            $contactLine,
            $phoneLine,
            $addressLine,
            ($order->shipping['lat'] ?? null) && ($order->shipping['lng'] ?? null)
                ? '[📍 Open in Google Maps](https://www.google.com/maps/search/?api=1&query=' . $order->shipping['lat'] . ',' . $order->shipping['lng'] . ')'
                : null,
            '[🔗 View Order in Dashboard](' . rtrim(config('app.url', 'https://tronmatixcomputer.com'), '/') . '/dashboard/orders/' . $order->id . ')',
            $scheduleLine,
            '💳 Payment: ' . ($isPickup && $order->payment_method === 'cash'
                ? 'CASH AT STORE'
                : strtoupper($order->payment_method)),
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
            '🕐 ' . $order->created_at->format('d M Y, H:i'),
        ], fn($line) => $line !== null);

        $this->send(implode("\n", $lines));
    }

    /** Notify when admin confirms delivery / pickup. */
    public function sendDeliveryConfirmed(Order $order): void
    {
        if (!$this->token)
            return;

        $isPickup = $order->isPickup();
        $verb = $isPickup ? 'Picked Up' : 'Delivered';
        $icon = $isPickup ? '🏪' : '📦';

        $message = implode("\n", [
            "{$icon} *Order {$verb}!*",
            '',
            "📦 Order `#{$order->order_id}` has been {$verb}.",
            '👤 Customer: ' . ($order->user?->username ?? 'Guest'),
            '🕐 Confirmed: ' . now()->format('d M Y, H:i'),
        ]);

        $this->send($message);

        if ($order->user?->telegram_chat_id) {
            $this->send($message, $order->user->telegram_chat_id);
        }
    }

    /**
     * Payment confirmed via ABA BAKONG QR — notify admin with full order details.
     * Called from CheckPaymentController after PayWay confirms the transaction.
     */
    public function sendPaymentConfirmed(Order $order, string $apv): void
    {
        if (!$this->token)
            return;

        // Eager-load items if not already loaded
        if (!$order->relationLoaded('items')) {
            $order->load('items');
        }

        $isPickup = $order->isPickup();
        $fulfillment = $isPickup ? '🏪 STORE PICKUP' : '🚚 DELIVERY';

        $itemLines = $order->items->map(function ($item) {
            $lineTotal = number_format($item->price * $item->qty, 2);
            $warranty = '';
            if ($item->warranty_start && $item->warranty_end) {
                $warranty = "\n     🛡 Warranty: " . $item->warranty_start->format('d.m.Y')
                    . ' - ' . $item->warranty_end->format('d.m.Y');
            }
            return "  • {$item->name} ×{$item->qty}  →  \${$lineTotal}{$warranty}";
        })->join("\n");

        $lines = array_filter([
            '✅ *ABA BAKONG Payment Confirmed!*',
            '',
            "📦 Order: `#{$order->order_id}`",
            '👤 Customer: ' . ($order->user?->username ?? 'Guest'),
            '📞 Phone: ' . ($order->user?->phone ?? '—'),
            $fulfillment,
            $order->delivery_date
            ? '🗓 ' . ($isPickup ? 'Pickup' : 'Delivery') . ': ' . $order->delivery_date
            . ($order->delivery_time_slot ? ' | ' . $order->delivery_time_slot : '')
            : null,
            '',
            '*Items Paid:*',
            $itemLines,
            '',
            ($order->subtotal && (float) $order->subtotal !== (float) $order->total)
            ? "💰 Subtotal: \${$order->subtotal}"
            : null,
            ($order->discount_amount ?? 0) > 0
            ? "🏷 Discount ({$order->discount_code}): −\${$order->discount_amount}"
            : null,
            "✅ *Total Paid: \${$order->total} USD*",
            "🔑 APV: `{$apv}`",
            '',
            '🕐 ' . now()->format('d M Y, H:i'),
        ], fn($l) => $l !== null);

        $this->send(implode("\n", $lines));
    }

    /** Send a plain text alert (for generic events). */
    public function sendAlert(string $text): void
    {
        if (!$this->token)
            return;
        $this->send($text);
    }

    /**
     * sendMessage() alias — several controllers called ->sendMessage() which
     * didn't exist, causing PHP0418 "Call to unknown method" fatal errors.
     */
    public function sendMessage(string $text, ?string $chatId = null): void
    {
        if (!$this->token)
            return;
        $this->send($text, $chatId);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

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

                if (!$res->successful()) {
                    \Illuminate\Support\Facades\Log::error(
                        'Telegram API error: ' . $res->status() . ' — ' . $res->body()
                    );
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning(
                    "Telegram send failed (chat_id={$target}): " . $e->getMessage()
                );
            }
        }
    }
}
