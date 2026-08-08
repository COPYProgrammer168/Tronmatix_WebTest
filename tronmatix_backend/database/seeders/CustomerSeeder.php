<?php

// database/seeders/CustomerSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLocation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    // ── Real locations in Cambodia with lat/lng ────────────────────────────────
    private array $locations = [
        // Phnom Penh districts
        ['address' => 'St. 310, Boeng Keng Kang I, Phnom Penh',       'city' => 'Phnom Penh', 'lat' => 11.547,  'lng' => 104.919],
        ['address' => 'Norodom Blvd, Chamkarmon, Phnom Penh',         'city' => 'Phnom Penh', 'lat' => 11.556,  'lng' => 104.928],
        ['address' => 'Monivong Blvd, 7 Makara, Phnom Penh',          'city' => 'Phnom Penh', 'lat' => 11.565,  'lng' => 104.913],
        ['address' => 'Russian Federation Blvd, Toul Kork, Phnom Penh','city' => 'Phnom Penh', 'lat' => 11.574,  'lng' => 104.896],
        ['address' => 'St. 2004, Chroy Changvar, Phnom Penh',         'city' => 'Phnom Penh', 'lat' => 11.601,  'lng' => 104.930],
        ['address' => 'St. 271, Tuol Kork, Phnom Penh',               'city' => 'Phnom Penh', 'lat' => 11.562,  'lng' => 104.878],
        ['address' => 'Kampuchea Krom Blvd, Meanchey, Phnom Penh',    'city' => 'Phnom Penh', 'lat' => 11.539,  'lng' => 104.945],
        ['address' => 'St. 1003, Dangkao, Phnom Penh',                'city' => 'Phnom Penh', 'lat' => 11.509,  'lng' => 104.889],
        // Siem Reap
        ['address' => 'Pub Street, Siem Reap',                        'city' => 'Siem Reap',  'lat' => 13.362,  'lng' => 103.859],
        ['address' => 'Sivatha Blvd, Siem Reap',                      'city' => 'Siem Reap',  'lat' => 13.358,  'lng' => 103.853],
        // Battambang
        ['address' => 'St. 1, Battambang',                            'city' => 'Battambang', 'lat' => 13.102,  'lng' => 103.198],
        ['address' => 'Riverside Road, Battambang',                   'city' => 'Battambang', 'lat' => 13.095,  'lng' => 103.196],
        // Sihanoukville
        ['address' => 'Ekareach St, Sihanoukville',                   'city' => 'Sihanoukville', 'lat' => 10.632, 'lng' => 103.523],
        ['address' => 'Victory Beach Road, Sihanoukville',            'city' => 'Sihanoukville', 'lat' => 10.649, 'lng' => 103.493],
        // Kampong Cham
        ['address' => 'St. 5, Kampong Cham',                          'city' => 'Kampong Cham', 'lat' => 12.001, 'lng' => 105.463],
        // Kampot
        ['address' => 'Old Market Area, Kampot',                      'city' => 'Kampot',     'lat' => 10.611,  'lng' => 104.179],
        // Kratie
        ['address' => 'Kratie Riverfront, Kratie',                    'city' => 'Kratie',     'lat' => 12.481,  'lng' => 106.019],
        // Takeo
        ['address' => 'Central Market, Takeo',                        'city' => 'Takeo',      'lat' => 10.983,  'lng' => 104.783],
        // Kampong Speu
        ['address' => 'Kampong Speu Town Center',                     'city' => 'Kampong Speu', 'lat' => 11.453, 'lng' => 104.521],
        // Pursat
        ['address' => 'Pursat Market Road, Pursat',                   'city' => 'Pursat',     'lat' => 12.538,  'lng' => 103.919],
    ];

    private array $firstNames = [
        'Sokha', 'Dara', 'Chanthy', 'Visal', 'Sreymom', 'Bopha', 'Rathana',
        'Kimheng', 'Sophal', 'Makara', 'Chan', 'Vanna', 'Samnang', 'Sopheak',
        'Chenda', 'Navy', 'Borey', 'Phalla', 'Sarom', 'Thida', 'Khemara',
        'Chantha', 'Rithy', 'Sothea', 'Sreyleak', 'Piseth', 'Lida', 'Kosal',
        'Virak', 'Sreyla', 'Bunna', 'Sovann', 'Ratha', 'Mony', 'Kimhak',
        'Veasna', 'Narith', 'Channary', 'Srey', 'Sophea',
    ];

    private array $lastNames = [
        'Chea', 'Ly', 'Kim', 'Peng', 'Noun', 'Sann', 'Hok', 'Tep', 'Sao',
        'Meas', 'Oum', 'Yim', 'Nuon', 'Keo', 'Chhoeun', 'Chan', 'Seng',
        'Kuy', 'Hour', 'Chhim', 'Bun', 'In', 'Yin', 'Lim', 'Kong',
        'Sok', 'Heng', 'Pov', 'Ros', 'Men', 'Phal', 'Nget', 'Touch',
    ];

    private array $domains = [
        'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com',
    ];

    private function generatePhone(): string
    {
        $prefixes = ['010', '011', '012', '015', '016', '017', '018',
                     '061', '066', '067', '068', '069', '070', '076',
                     '077', '078', '081', '084', '085', '086', '087',
                     '088', '089', '090', '092', '093', '095', '096',
                     '097', '098', '099'];
        $prefix = $prefixes[array_rand($prefixes)];
        $num = '';
        for ($i = 0; $i < 7; $i++) {
            $num .= rand(0, 9);
        }

        return $prefix . ' ' . substr($num, 0, 3) . ' ' . substr($num, 3);
    }

    public function run(): void
    {
        $created = 0;

        for ($i = 0; $i < 50; $i++) {
            $firstName = $this->firstNames[array_rand($this->firstNames)];
            $lastName  = $this->lastNames[array_rand($this->lastNames)];
            $name      = $firstName . ' ' . $lastName;
            $username  = strtolower($firstName . $lastName) . rand(10, 999);
            $email     = $username . '@' . $this->domains[array_rand($this->domains)];

            $createdAt = Carbon::now()
                ->subMonths(rand(0, 12))
                ->subDays(rand(0, 28));

            // Create customer
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'       => $name,
                    'username'   => $username,
                    'password'   => Hash::make('Password@123'),
                    'phone'      => $this->generatePhone(),
                    'role'       => 'customer',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]
            );

            // Give each customer 1–2 locations with lat/lng
            $locCount = rand(1, 2);
            for ($j = 0; $j < $locCount; $j++) {
                $loc = $this->locations[array_rand($this->locations)];
                UserLocation::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'address' => $loc['address'],
                    ],
                    [
                        'name'        => $j === 0 ? 'Home' : 'Office',
                        'phone'       => $this->generatePhone(),
                        'city'        => $loc['city'],
                        'country'     => 'Cambodia',
                        'note'        => $j === 0 ? null : (rand(0, 1) ? 'Call before delivery' : null),
                        'is_default'  => $j === 0,
                        'lat'         => $loc['lat'] + (rand(-50, 50) / 1000),  // slight jitter
                        'lng'         => $loc['lng'] + (rand(-50, 50) / 1000),
                        'map_address' => $loc['address'],
                    ]
                );
            }

            $created++;
        }

        $this->command->info("✅ CustomerSeeder: {$created} customers with locations created.");
        $this->command->info('   Default password: Password@123');
    }
}
