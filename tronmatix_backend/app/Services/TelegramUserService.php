<?php

// app/Services/TelegramUserService.php
// FIX: Switched from MarkdownV2 to HTML parse_mode.
// NEW:  onPaymentConfirmed() — notifies the user when BAKONG QR payment is confirmed.

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramUserService
{
    private string $token;
    private string $apiBase;
    private string $ownerChatId;

    public function __construct()
    {
        $this->token       = config('services.telegram_user.bot_token', '');
        $this->ownerChatId = config('services.telegram_user.chat_id', '');
        $this->apiBase     = "https://api.telegram.org/bot{$this->token}";
    }

    // =========================================================================
    //  AUTH / CONNECTION
    // =========================================================================

    public function verifyLoginHash(array $data): bool
    {
        if (empty($data['hash']) || ! $this->token) return false;

        $hash      = $data['hash'];
        $checkData = $data;
        unset($checkData['hash']);

        ksort($checkData);
        $checkString = implode("\n", array_map(
            fn($k, $v) => "{$k}={$v}",
            array_keys($checkData),
            array_values($checkData)
        ));

        $secretKey    = hash('sha256', $this->token, true);
        $expectedHash = hash_hmac('sha256', $checkString, $secretKey);

        if (! hash_equals($expectedHash, $hash)) return false;

        $age = time() - (int) ($data['auth_date'] ?? 0);
        if ($age > 86400) {
            Log::warning('[UserBot] auth_date too old', ['age_seconds' => $age]);
            return false;
        }

        return true;
    }

    public function connectUser(User $user, array $telegramData): void
    {
        $user->update([
            'telegram_chat_id'      => (string) $telegramData['id'],
            'telegram_username'     => $telegramData['username'] ?? null,
            'telegram_connected_at' => now(),
        ]);
        $this->sendWelcomeMessage($user);
    }

    public function disconnectUser(User $user): void
    {
        if ($user->telegram_chat_id) {
            $this->sendDisconnectAlert($user);
        }
        $user->update([
            'telegram_chat_id'      => null,
            'telegram_username'     => null,
            'telegram_connected_at' => null,
        ]);
    }

    // =========================================================================
    //  CONNECTION MESSAGES
    // =========================================================================

    public function sendWelcomeMessage(User $user): void
    {
        if (! $user->telegram_chat_id) return;

        $uname    = $this->e('@'.($user->telegram_username ?? $user->username ?? 'there'));
        $siteName = $this->e(config('app.name', 'Tronmatix'));

        $this->sendToUser($user->telegram_chat_id, implode("\n", [
            "🎉 <b>Welcome to {$siteName} Notifications!</b>", '',
            "Hi {$uname}! Your Telegram is now connected.", '',
            '<b>You will receive:</b>',
            '🧾 Order receipts after checkout',
            '✅ Payment confirmations',
            '🚚 Shipping &amp; delivery updates',
            '🚫 Cancellation notifications', '',
            'To disconnect, go to your profile settings on our website.',
            '🕐 '.$this->ts(),
        ]));
    }

    public function sendDisconnectAlert(User $user): void
    {
        if (! $user->telegram_chat_id) return;

        $uname    = $this->e('@'.($user->telegram_username ?? $user->username ?? 'there'));
        $siteName = $this->e(config('app.name', 'Tronmatix'));

        $this->sendToUser($user->telegram_chat_id, implode("\n", [
            "⚠️ <b>Telegram Disconnected</b>", '',
            "Hi {$uname}, your Telegram account has been",
            "disconnected from <b>{$siteName}</b>.", '',
            '🚫 You will no longer receive order notifications here.', '',
            'To reconnect, visit your profile settings on our website.',
            '🕐 '.$this->ts(),
        ]));
    }

    // =========================================================================
    //  ORDER EVENT NOTIFICATIONS
    // =========================================================================

    public function onOrderPlaced(Order $order): void
    {
        if ($tgId = $order->user?->telegram_chat_id) {
            $this->sendToUser($tgId, $this->buildUserReceiptMessage($order));
        }
    }

    public function onOrderConfirmed(Order $order): void
    {
        if (! $tgId = $order->user?->telegram_chat_id) return;
        $id    = $this->e($order->order_id ?? (string) $order->id);
        $total = $this->e((string) $order->total);

        $this->sendToUser($tgId, implode("\n", [
            '✅ <b>Your order has been confirmed!</b>', '',
            "📦 Order: <code>#{$id}</code>",
            "💰 Total: \${$total}", '',
            "We're preparing your items for dispatch.",
            '🕐 '.$this->ts(),
        ]));
    }

    public function onOrderShipped(Order $order): void
    {
        if (! $tgId = $order->user?->telegram_chat_id) return;
        $id    = $this->e($order->order_id ?? (string) $order->id);
        $total = $this->e((string) $order->total);

        $lines = ['🚚 <b>Your order is on its way!</b>', '',
            "📦 Order: <code>#{$id}</code>", "💰 Total: \${$total}"];

        if ($order->delivery_date) {
            $slot    = $order->delivery_time_slot ? ' | '.$this->e($order->delivery_time_slot) : '';
            $lines[] = '🗓 Expected delivery: '.$this->e($order->delivery_date).$slot;
        }
        $lines[] = '';
        $lines[] = '📍 Please be ready to receive your order!';
        $lines[] = '🕐 '.$this->ts();

        $this->sendToUser($tgId, implode("\n", $lines));
    }

    public function onOrderDelivered(Order $order): void
    {
        if (! $tgId = $order->user?->telegram_chat_id) return;
        $id    = $this->e($order->order_id ?? (string) $order->id);
        $total = $this->e((string) $order->total);

        $this->sendToUser($tgId, implode("\n", [
            '🎉 <b>Order Delivered!</b>', '',
            "📦 Order <code>#{$id}</code> has been delivered.",
            "💰 Total paid: \${$total}", '',
            '💙 Thank you for shopping with us! We hope you love your order.',
            '🕐 '.$this->ts(),
        ]));
    }

    public function onOrderCancelled(Order $order): void
    {
        if (! $tgId = $order->user?->telegram_chat_id) return;
        $id    = $this->e($order->order_id ?? (string) $order->id);
        $total = $this->e((string) $order->total);

        $this->sendToUser($tgId, implode("\n", [
            '🚫 <b>Order Cancelled</b>', '',
            "📦 Order <code>#{$id}</code> has been cancelled.",
            "💰 Amount: \${$total}", '',
            'If you have questions, please contact our support.',
            '🕐 '.$this->ts(),
        ]));
    }

    /**
     * NEW: Notify user when their BAKONG QR payment is confirmed.
     * Called from CheckPaymentController after PayWay verifies the transaction.
     */
    public function onPaymentConfirmed(Order $order, string $apv): void
    {
        if (! $tgId = $order->user?->telegram_chat_id) return;

        // Eager-load items if not already loaded
        if (! $order->relationLoaded('items')) {
            $order->load('items');
        }

        $id    = $this->e($order->order_id ?? (string) $order->id);
        $total = number_format((float) $order->total, 2);
        $apvE  = $this->e($apv);

        $isPickup    = $order->isPickup();
        $fulfillment = $isPickup
            ? '🏪 You selected <b>Store Pickup</b> — please come to collect your order.'
            : '🚚 Your order will be <b>delivered</b> to your address.';

        $itemLines = $order->items->map(function ($item) {
            $lineTotal = number_format($item->price * $item->qty, 2);
            $name      = $this->e($item->name);
            return "  • {$name} ×{$item->qty} → \${$lineTotal}";
        })->join("\n");

        $lines = array_filter([
            '✅ <b>Payment Confirmed!</b>', '',
            "📦 Order: <code>#{$id}</code>",
            "💳 Paid via: <b>ABA BAKONG QR</b>",
            "🔑 Reference: <code>{$apvE}</code>",
            '',
            '<b>Items paid:</b>',
            $itemLines,
            '',
            ($order->discount_amount ?? 0) > 0
                ? "🏷 Discount: −\$" . $this->e((string) $order->discount_amount)
                : null,
            "✅ <b>Total Paid: \${$total} USD</b>",
            '',
            $fulfillment,
            $order->delivery_date
                ? '🗓 ' . ($isPickup ? 'Pickup' : 'Delivery') . ': '
                    . $this->e($order->delivery_date)
                    . ($order->delivery_time_slot ? ' | ' . $this->e($order->delivery_time_slot) : '')
                : null,
            '',
            "Thank you for your payment! We'll start preparing your order now. 💙",
            '🕐 '.$this->ts(),
        ], fn ($l) => $l !== null);

        $this->sendToUser($tgId, implode("\n", $lines));
    }

    public function sendReceiptToUser(Order $order): void
    {
        if ($tgId = $order->user?->telegram_chat_id) {
            $this->sendToUser($tgId, $this->buildUserReceiptMessage($order));
        }
    }

    public function sendTestMessage(User $user): bool
    {
        if (! $user->telegram_chat_id) return false;
        $uname = $this->e('@'.($user->telegram_username ?? 'unknown'));

        return $this->sendToUser($user->telegram_chat_id, implode("\n", [
            '👋 <b>Test Message</b>', '',
            'Your Telegram notifications are working correctly!',
            "🆔 Connected as: {$uname}",
            '🕐 '.$this->ts(),
        ]));
    }

    /** Notify user when their phone number is missing from the order. */
    public function sendPhoneMissingUserAlert(Order $order): void
    {
        if (! $tgId = $order->user?->telegram_chat_id) return;

        $this->sendToUser($tgId, implode("\n", [
            '⚠️ <b>Missing Contact Information</b>', '',
            "Hi, we noticed that you didn't provide a phone number for your order <code>#{$order->order_id}</code>.",
            '',
            'Please contact our support or update your order details so we can reach you for delivery/pickup.',
            '🕐 '.$this->ts(),
        ]));
    }

    // =========================================================================
    //  PRIVATE HELPERS
    // =========================================================================

    private function sendToUser(string $chatId, string $text): bool
    {
        if (! $this->token) {
            Log::warning('[UserBot] TELEGRAM_USER_BOT_TOKEN not set.');
            return false;
        }

        try {
            $res = Http::timeout(8)->withoutVerifying()
                ->post("{$this->apiBase}/sendMessage", [
                    'chat_id'    => $chatId,
                    'text'       => $text,
                    'parse_mode' => 'HTML',
                ]);

            if (! $res->successful()) {
                Log::error('[UserBot] sendMessage failed', [
                    'chat_id'      => $chatId,
                    'status'       => $res->status(),
                    'body'         => $res->body(),
                    'text_preview' => substr($text, 0, 200),
                ]);
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            Log::warning('[UserBot] sendMessage exception: '.$e->getMessage());
            return false;
        }
    }

    private function buildUserReceiptMessage(Order $order): string
    {
        $shipping = $order->shipping;
        if (is_string($shipping)) $shipping = json_decode($shipping, true) ?? [];

        $id       = $this->e($order->order_id ?? (string) $order->id);
        $address  = $this->e(
            ($shipping['address'] ?? '—')
            . (isset($shipping['city']) && $shipping['city'] ? ', '.$shipping['city'] : '')
        );
        $method   = $this->e(strtoupper($order->payment_method ?? ''));
        $subtotal = $this->e((string) ($order->subtotal ?? $order->total));
        $total    = $this->e((string) $order->total);

        $itemLines = $order->items->map(function ($item) {
            $lineTotal = round($item->price * $item->qty, 2);
            $name      = $this->e($item->name);
            $warranty  = '';
            if ($item->warranty_start && $item->warranty_end) {
                $warranty = "\n     🛡 Warranty: " . $item->warranty_start->format('d.m.Y')
                          . ' - ' . $item->warranty_end->format('d.m.Y');
            }
            return "  • {$name} ×{$item->qty} → \${$lineTotal}{$warranty}";
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
                ? '🏷 Discount ('.$this->e($order->discount_code ?? '').'): -$'.$this->e((string) $order->discount_amount)
                : null,
            "✅ <b>Total: \${$total}</b>", '',
            "We'll notify you when your order ships. 💙",
            '🕐 '.$this->ts(),
        ], fn($l) => $l !== null);

        return implode("\n", $lines);
    }

    private function e(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function ts(): string
    {
        return now()->format('d M Y, H:i');
    }
}
