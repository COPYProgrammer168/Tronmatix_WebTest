<?php

namespace Database\Seeders;

use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

Class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $roles = ['editor', 'seller', 'delivery', 'developer'];
        $khmerNames = [
            'Sokha Chan', 'Dara Meas', 'Maly Heng', 'Vireak Som', 'Seiha Pen',
            'Kannika Lim', 'Sophal Keo', 'Chantra Yin', 'Ratana Bun', 'Sutha Nuon'
        ];
        $staffMembers = [];

        for ($i = 0; $i < 10; $i++) {
            $role = $roles[array_rand($roles)];
            $staffMembers[] = [
                'name' => $khmerNames[$i],
                'email' => 'staff' . ($i + 1) . '@tronmatix.com',
                'username' => 'staff' . ($i + 1),
                'password' => Hash::make('Staff@1234'),
                'role' => $role,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Staff::insert($staffMembers);

        $this->command->info('✅ Staff seeded:  ' . Staff::count());
        $this->command->info('   Default password: Staff@1234');
    }
}