<?php

// database/seeders/ActivityLogSeeder.php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeds login activity (login_success / login_failed) so the Activity Log page
 * has data, including the CURRENT month (e.g. August 2026). ~40% of rows land
 * in the current month so it always shows recent activity; the rest spread
 * across the prior 11 months.
 */
class ActivityLogSeeder extends Seeder
{
    private array $ipPool = [
        '103.79.98.10', '36.37.192.4', '110.74.192.5', '175.100.34.20',
        '202.79.46.2', '103.53.21.8', '45.126.98.5', '118.69.20.7',
        '203.144.90.1', '111.65.33.9', '110.137.180.2', '1.32.22.1',
    ];

    private array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/125.0',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148',
        'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 Mobile Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/126.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Edge/126.0',
    ];

    public function run(): void
    {
        $users  = User::all();
        $admins = Admin::all();
        $staff  = Staff::all();

        $count = 0;

        // Generate ~200 login events across the last 12 months.
        for ($i = 0; $i < 200; $i++) {
            if (rand(0, 100) < 40) {
                // Current month — within the last N days of this month.
                $at = Carbon::now()
                    ->subDays(rand(0, min(now()->day - 1, 28)))
                    ->subHours(rand(0, 23))
                    ->subMinutes(rand(0, 59));
            } else {
                // Prior 11 months.
                $at = Carbon::now()
                    ->subMonths(rand(1, 11))
                    ->subDays(rand(0, 28))
                    ->subHours(rand(0, 23))
                    ->subMinutes(rand(0, 59));
            }

            // Weight: ~85% success, ~15% failed.
            $success = rand(0, 100) < 85;

            // Pick a random actor across the three account types.
            $actorPool = collect();
            $actorPool = $actorPool->merge($users->map(fn ($u) => ['type' => 'user', 'model' => $u]))
                ->merge($admins->map(fn ($a) => ['type' => 'admin', 'model' => $a]))
                ->merge($staff->map(fn ($s) => ['type' => 'staff', 'model' => $s]));

            if ($actorPool->isEmpty()) {
                $this->command->warn('⚠️  No users/admins/staff found — run User/Customer/Staff seeders first.');
                return;
            }

            $actor = $actorPool->random();

            $guard = $actor['type'] === 'admin' ? 'admin' : ($actor['type'] === 'staff' ? 'staff' : 'web');
            $actorLabel = ucfirst($actor['type']);

            $details = $success
                ? ['guard' => $guard, 'role' => $actor['model']->role ?? null]
                : ['reason' => $this->randomFailReason()];

            ActivityLog::create([
                'actor_id'    => $actor['model']->id,
                'actor_type'  => $actorLabel,
                'actor_name'  => $actor['model']->name ?? $actor['model']->email,
                'action'      => $success ? 'login_success' : 'login_failed',
                'entity_type' => $actor['type'] === 'admin' ? 'Admin' : ($actor['type'] === 'staff' ? 'Staff' : 'User'),
                'entity_id'   => $actor['model']->id,
                'entity_name' => $actor['model']->email ?? $actor['model']->name,
                'details'     => $details,
                'ip_address'  => $this->ipPool[array_rand($this->ipPool)],
                'user_agent'  => $this->userAgents[array_rand($this->userAgents)],
                'created_at'  => $at,
                'updated_at'  => $at,
            ]);

            $count++;
        }

        $currentMonth = ActivityLog::where('action', 'like', 'login_%')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $this->command->info("✅ ActivityLogSeeder: {$count} login events created.");
        $this->command->info('   Current month (' . now()->format('Y-m') . ") login events: {$currentMonth}");
    }

    private function randomFailReason(): string
    {
        $reasons = ['invalid_credentials', 'account_deactivated', 'no_match', 'too_many_attempts'];

        return $reasons[array_rand($reasons)];
    }
}
