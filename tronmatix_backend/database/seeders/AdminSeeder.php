<?php

namespace Database\Seeders;

use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        Admin::insert([
            [
                'name' => 'Super Admin',
                'email' => 'admin@tronmatix.com',
                'username' => 'admin',
                'password' => Hash::make('Admin@1234'),
                'role' => 'superadmin',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Editor Staff',
                'email' => 'editor@tronmatix.com',
                'username' => 'editor',
                'password' => Hash::make('Editor@1234'),
                'role' => 'editor',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->command->info('✅ Admins seeded:  '.Admin::count());
        $this->command->info('   Username: admin    | Password: Admin@1234');
        $this->command->info('   Username: editor   | Password: Editor@1234');
    }
}
