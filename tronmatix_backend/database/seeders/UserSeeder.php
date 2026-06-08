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
            ['name' => 'Test User', 'username' => 'testuser', 'email' => 'test@tronmatix.com', 'months_ago' => 12, 'password' => 'Test@1234'],

            // Cambodian sample users spread across the last 11 months
            ['name' => 'Sokha', 'username' => 'sokha',   'email' => 'sokha@gmail.com',   'months_ago' => 11],
            ['name' => 'Dara', 'username' => 'dara',    'email' => 'dara@gmail.com',    'months_ago' => 10],
            ['name' => 'Chanthy', 'username' => 'chanthy', 'email' => 'chanthy@gmail.com', 'months_ago' => 9],
            ['name' => 'Visal', 'username' => 'visal',   'email' => 'visal@gmail.com',   'months_ago' => 8],
            ['name' => 'Sreymom', 'username' => 'sreymom', 'email' => 'sreymom@gmail.com', 'months_ago' => 7],
            ['name' => 'Bopha', 'username' => 'bopha',   'email' => 'bopha@gmail.com',   'months_ago' => 5],
            ['name' => 'Rathana', 'username' => 'rathana', 'email' => 'rathana@gmail.com', 'months_ago' => 4],
            ['name' => 'Kimheng', 'username' => 'kimheng', 'email' => 'kimheng@gmail.com', 'months_ago' => 3],
            ['name' => 'Sophal', 'username' => 'sophal',  'email' => 'sophal@gmail.com',  'months_ago' => 2],
            ['name' => 'Makara', 'username' => 'makara',  'email' => 'makara@gmail.com',  'months_ago' => 0],
        ];

        foreach ($users as $data) {
            $createdAt = Carbon::now()
                ->subMonths($data['months_ago'])
                ->addDays(rand(0, 20));

            // updateOrCreate — safe to re-run without duplicate-email crashes
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
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

        // Create 50 random users
        $firstNames = ['Sokha', 'Dara', 'Chanthy', 'Visal', 'Sreymom', 'Bopha', 'Rathana', 'Kimheng', 'Sophal', 'Makara', 'Chan', 'Vanna', 'Samnang', 'Sopheak', 'Chenda', 'Navy', 'Borey', 'Phalla', 'Sarom', 'Thida', 'Khemara', 'Chantha', 'Rithy', 'Sothea', 'Sreyleak'];
        $lastNames = ['Chea', 'Ly', 'Kim', 'Peng', 'Noun', 'Sann', 'Hok', 'Tep', 'Sao', 'Meas', 'Oum', 'Yim', 'Nuon', 'Keo', 'Chhoeun', 'Chan', 'Seng', 'Kuy', 'Hour', 'Chhim', 'Bun', 'In', 'Yin', 'Lim', 'Kong'];
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com'];

        for ($i = 0; $i < 50; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $name = $firstName . ' ' . $lastName;

            // Generate a simple username from the name
            $username = strtolower($firstName . $lastName) . rand(10, 99);
            $email = $username . '@' . $domains[array_rand($domains)];

            $createdAt = Carbon::now()->subMonths(rand(0, 12))->subDays(rand(0, 28));

            User::updateOrCreate(
                ['email' => $email],
                [
                    'username' => $username,
                    'name' => $name,
                    'password' => Hash::make('Password@123'),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );
        }
        $this->command->info('✅ Added 50 random users with names.');
    }
}