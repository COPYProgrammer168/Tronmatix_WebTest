<?php

// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();

        $users = [
            ['name' => 'Test User', 'username' => 'testuser', 'email' => 'test@tronmatix.com', 'months_ago' => 12, 'password' => 'Test@1234'],

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

            // Stagger last_login: each user logged in at least once, most within
            // the last 30 days. recent_login is a separate "today/yesterday"
            // login so dashboard "recently logged in" shows activity.
            $lastLogin = Carbon::now()->subDays(rand(0, 30));
            $recentLogin = rand(0, 4) === 0
                ? Carbon::now()->subDays(rand(0, 3))   // 20% also logged in recently
                : null;

            // updateOrCreate — safe to re-run without duplicate-email crashes
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'password' => Hash::make($data['password'] ?? 'Password@123'),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'last_login_at' => $lastLogin,
                    'recent_login_at' => $recentLogin,
                ]
            );
        }

        $this->command->info('✅ UserSeeder:  '.User::count().' users');
        $this->command->info('   Default password : Password@123');
        $this->command->info('   Test user password: Test@1234');

        // ── Create 100 realistic random users ────────────────────────────────────
        $firstNames = [
            'Sokha','Dara','Chanthy','Visal','Sreymom','Bopha','Rathana','Kimheng',
            'Sophal','Makara','Chan','Vanna','Samnang','Sopheak','Chenda','Navy',
            'Borey','Phalla','Sarom','Thida','Khemara','Chantha','Rithy','Sothea',
            'Sreyleak','Pisey','Rattanak','Mony','Davin','Sreyneang','Phearom',
            'Tola','Chanmony','Sreypov','Bopha','Kunthea','Sopheap','Chhun',
            'Sokun','Meng','Sreyroth','Heng','Nara','Phin','Thira','Leap','Vichet',
            'Sokuntheary','Chamnan','Sreynith','Ravy','Dina','Cheat','Kimleng',
        ];
        $lastNames = [
            'Chea','Ly','Kim','Peng','Noun','Sann','Hok','Tep','Sao','Meas',
            'Oum','Yim','Nuon','Keo','Chhoeun','Chan','Seng','Kuy','Hour','Chhim',
            'Bun','In','Yin','Lim','Kong','Heng','Phorn','Rith','Soeun','Lorn',
        ];
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com'];
        $roles = ['customer', 'customer', 'customer', 'vip', 'reseller']; // weighted toward customer

        for ($i = 0; $i < 100; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName  = $lastNames[array_rand($lastNames)];
            $name      = $firstName . ' ' . $lastName;

            $username = strtolower($firstName . $lastName) . rand(10, 99);
            $email    = $username . '@' . $domains[array_rand($domains)];

            // Accounts spread across the last 18 months
            $createdAt = Carbon::now()->subMonths(rand(0, 18))->subDays(rand(0, 28));

            // last_login: spread across the last 60 days — most active recently,
            // a few haven't logged in for weeks (realistic churn).
            $lastLoginRand = rand(1, 100);
            if ($lastLoginRand <= 60) {
                $lastLogin = Carbon::now()->subDays(rand(0, 7));   // active this week
            } elseif ($lastLoginRand <= 85) {
                $lastLogin = Carbon::now()->subDays(rand(8, 30));  // active this month
            } else {
                $lastLogin = Carbon::now()->subDays(rand(31, 60)); // dormant
            }

            // recent_login: ~30% of users have a second "very recent" login
            // (today or yesterday) to drive the dashboard "recently logged in" list.
            $recentLogin = rand(0, 2) === 0
                ? Carbon::now()->subDays(rand(0, 2))->subHours(rand(0, 23))
                : null;

            User::updateOrCreate(
                ['email' => $email],
                [
                    'username'       => $username,
                    'name'           => $name,
                    'role'           => $roles[array_rand($roles)],
                    'password'       => Hash::make('Password@123'),
                    'created_at'    => $createdAt,
                    'updated_at'    => $createdAt,
                    'last_login_at'  => $lastLogin,
                    'recent_login_at' => $recentLogin,
                ]
            );
        }
        $this->command->info('✅ Added 100 random users with realistic login activity.');
    }
}