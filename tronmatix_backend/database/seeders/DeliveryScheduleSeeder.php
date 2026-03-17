<?php

// database/seeders/DeliveryScheduleSeeder.php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $rows = [];

        // Monday (1) through Saturday (6) — two slots per day
        // Sunday (0) is closed / no delivery
        foreach (range(1, 6) as $day) {
            $rows[] = [
                'day_of_week' => $day,
                'time_start' => '08:00:00',
                'time_end' => '12:00:00',
                'is_available' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $rows[] = [
                'day_of_week' => $day,
                'time_start' => '13:00:00',
                'time_end' => '17:00:00',
                'is_available' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('delivery_schedules')->insert($rows);

        $this->command->info('✅  DeliveryScheduleSeeder: Mon–Sat, 2 slots/day ('.count($rows).' rows).');
    }
}
