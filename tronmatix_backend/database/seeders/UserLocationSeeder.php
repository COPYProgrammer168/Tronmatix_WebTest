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
        'Svay Rieng', 'Stung Treng', 'Pailin', 'Kep', 'Koh Kong',
        'Kampong Speu', 'Kampong Chhnang', 'Kampong Thom', 'Oddar Meanchey', 'Banteay Meanchey'
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

    private function generateKhmerPhoneNumber(): string
    {
        $prefixes = ['010', '011', '012', '015', '016', '017', '018', '061', '066', '067', '068', '069', '070', '076', '077', '078', '081', '084', '085', '086', '087', '088', '089', '090', '092', '093', '095', '096', '097', '098', '099'];
        $prefix = $prefixes[array_rand($prefixes)];
        
        // Generate 6 or 7 digits after prefix
        $length = (strlen($prefix) == 3) ? rand(6, 7) : 6;
        $number = '';
        for ($i = 0; $i < $length; $i++) {
            $number .= rand(0, 9);
        }
        
        return $prefix . ' ' . substr($number, 0, 3) . ' ' . substr($number, 3);
    }

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
                    'phone' => $this->generateKhmerPhoneNumber(),
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
