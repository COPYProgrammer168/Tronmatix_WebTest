<?php

// database/seeders/PaymentSeeder.php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds a payment record for every paid order (sold orders always carry at
 * least one confirmed payment; pending orders get a pending Bakong QR row).
 *
 * `provider` follows the order's payment_method (bakong/cash/card), status
 * mirrors the order status when paid, and tran_id stays unique. Re-seeding
 * truncates payments first so the set stays consistent with the orders.
 */
class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::count() === 0) {
            $this->command->warn('⚠️  PaymentSeeder: no orders yet — run OrderSeeder first.');
            return;
        }

        Payment::query()->delete();

        $created = 0;
        foreach (Order::with('user')->get() as $order) {
            $paid      = $order->isPaid();
            $status    = $paid ? Payment::STATUS_PAID : Payment::STATUS_PENDING;
            $method    = $order->payment_method ?? 'bakong';
            $paidAt    = $paid ? $order->created_at?->copy()->addMinutes(rand(2, 40)) : null;

            Payment::create([
                'order_id'        => $order->id,
                'tran_id'         => 'TRX-' . strtoupper(Str::random(8)),
                'provider'        => $method,
                'payment_method'  => $method,
                'amount'          => $order->total,
                'currency'        => 'USD',
                'qr_data'         => null,
                'qr_md5'          => md5(uniqid((string) $order->id, true)),
                'qr_expiration'   => null,
                'qr_expires_at'   => $paid ? null : $order->created_at?->copy()->addHours(24),
                'description'     => 'Order ' . $order->order_id . ($paid ? ' — paid' : ' — awaiting payment'),
                'status'          => $status,
                'paid'            => $paid,
                'paid_at'         => $paidAt,
                'expires_at'      => $paid ? null : $order->created_at?->copy()->addHours(24),
                'meta'            => ['seeded' => true, 'source' => 'PaymentSeeder'],
                'created_at'      => $order->created_at ?? Carbon::now(),
                'updated_at'      => $order->created_at ?? Carbon::now(),
            ]);
            $created++;
        }

        $paid = Payment::where('status', Payment::STATUS_PAID)->count();
        $this->command->info("✅ PaymentSeeder: {$created} payment records (paid: {$paid}).");
    }
}