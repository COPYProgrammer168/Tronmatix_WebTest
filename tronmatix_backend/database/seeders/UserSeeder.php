<?php

// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Known test account — always present, always the same credentials
            ['username' => 'testuser', 'email' => 'test@tronmatix.com', 'months_ago' => 12, 'password' => 'Test@1234'],

            // Cambodian sample users spread across the last 11 months
            ['username' => 'sokha',   'email' => 'sokha@gmail.com',   'months_ago' => 11],
            ['username' => 'dara',    'email' => 'dara@gmail.com',    'months_ago' => 10],
            ['username' => 'chanthy', 'email' => 'chanthy@gmail.com', 'months_ago' => 9],
            ['username' => 'visal',   'email' => 'visal@gmail.com',   'months_ago' => 8],
            ['username' => 'sreymom', 'email' => 'sreymom@gmail.com', 'months_ago' => 7],
            ['username' => 'bopha',   'email' => 'bopha@gmail.com',   'months_ago' => 5],
            ['username' => 'rathana', 'email' => 'rathana@gmail.com', 'months_ago' => 4],
            ['username' => 'kimheng', 'email' => 'kimheng@gmail.com', 'months_ago' => 3],
            ['username' => 'sophal',  'email' => 'sophal@gmail.com',  'months_ago' => 2],
            ['username' => 'makara',  'email' => 'makara@gmail.com',  'months_ago' => 0],
        ];

        foreach ($users as $data) {
            $createdAt = Carbon::now()
                ->subMonths($data['months_ago'])
                ->addDays(rand(0, 20));

            // updateOrCreate — safe to re-run without duplicate-email crashes
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'username' => $data['username'],
                    'password' => Hash::make($data['password'] ?? 'Password@123'),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );
        }

        $this->command->info('✅ UserSeeder:  '.User::count().' users');
        $this->command->info('   Default password : Password@123');
        $this->command->info('   Test user password: Test@1234');
    }
}
