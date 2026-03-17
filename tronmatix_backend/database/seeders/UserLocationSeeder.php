<?php

// database/seeders/UserLocationSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Database\Seeder;

class UserLocationSeeder extends Seeder
{
    private array $firstNames = [
        'Sophea', 'Dara', 'Bopha', 'Kosal', 'Chanthy', 'Virak', 'Sreyla', 'Piseth',
        'Lida', 'Makara', 'Ratana', 'Bunna', 'Chenda', 'Sovann', 'Ratha', 'Mony',
        'Kimhak', 'Veasna', 'Sothea', 'Narith', 'Channary', 'Phalla', 'Srey',
    ];

    private array $lastNames = [
        'Sok', 'Chan', 'Lim', 'Heng', 'Pov', 'Noun', 'Kim', 'Ly', 'Oun',
        'Seng', 'Tep', 'Ros', 'Keo', 'Men', 'Phal', 'Chea', 'Nget', 'Touch',
    ];

    private array $cities = [
        'Phnom Penh', 'Siem Reap', 'Battambang', 'Sihanoukville',
        'Kampong Cham', 'Kampot', 'Kratie', 'Pursat', 'Takeo', 'Prey Veng',
    ];

    private array $streets = [
        'St. 310, Boeng Keng Kang I',
        'St. 271, Tuol Kork',
        'Russian Federation Blvd, Toul Kork',
        'Norodom Blvd, Chamkarmon',
        'Monivong Blvd, 7 Makara',
        'St. 2004, Chroy Changvar',
        'National Road 4, Por Sen Chey',
        'St. 60, Meanchey',
        'Kampuchea Krom Blvd, 15 January',
        'St. 1003, Dangkao',
    ];

    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $isTestUser = ($user->email === 'test@tronmatix.com');
            $count = $isTestUser ? 2 : rand(1, 3);

            // Skip if already has locations (idempotent)
            if ($user->locations()->count() >= $count) {
                continue;
            }

            $existing = $user->locations()->count();

            for ($i = $existing; $i < $count; $i++) {
                $isDefault = ($i === 0);

                $name = $isTestUser
                    ? 'Test User'
                    : $this->firstNames[array_rand($this->firstNames)]
                        .' '.$this->lastNames[array_rand($this->lastNames)];

                $city = $this->cities[array_rand($this->cities)];

                UserLocation::create([
                    'user_id' => $user->id,
                    'name' => $name,
                    'phone' => '0'.rand(10, 19).' '.rand(100, 999).' '.rand(100, 999),
                    'address' => $this->streets[array_rand($this->streets)],
                    'city' => $city,
                    'country' => 'Cambodia',
                    'note' => $isDefault ? null : (rand(0, 1) ? 'Call before delivery' : null),
                    'is_default' => $isDefault,
                ]);
            }
        }

        $this->command->info('✅ UserLocationSeeder: '.UserLocation::count().' locations seeded.');
    }
}
