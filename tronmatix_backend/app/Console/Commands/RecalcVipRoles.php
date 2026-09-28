<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Order;
use App\Models\AdminSetting;
use Illuminate\Console\Command;

class RecalcVipRoles extends Command
{
    protected $signature = 'users:recalc-vip';
    protected $description = 'Recalculate VIP roles for all users based on total spent';

    public function handle(): int
    {
        $vipThreshold = (float) AdminSetting::get('vip_threshold', 5000);
        $this->info("VIP threshold: $vipThreshold");

        $users = User::whereIn('role', ['customer', 'vip'])->get();
        $updated = 0;

        foreach ($users as $user) {
            $totalSpent = Order::where('user_id', $user->id)
                ->whereNotIn('status', [Order::STATUS_CANCELLED])
                ->where('payment_status', 'paid')
                ->sum('total');

            $shouldBeVip = $totalSpent >= $vipThreshold;
            $isVip = $user->role === 'vip';

            if ($shouldBeVip && ! $isVip) {
                $user->update(['role' => 'vip']);
                $this->info("↑ {$user->username} → VIP (spent: $totalSpent)");
                $updated++;
            } elseif (! $shouldBeVip && $isVip) {
                $user->update(['role' => 'customer']);
                $this->warn("↓ {$user->username} → CUSTOMER (spent: $totalSpent)");
                $updated++;
            }
        }

        $this->info("Done. Updated $updated users.");
        return self::SUCCESS;
    }
}
