<?php

// database/seeders/UserLocationSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Database\Seeder;

/**
 * Seeds 1–3 delivery locations per user, all inside Cambodia with real
 * lat/lng coordinates (jittered slightly so map markers don't stack).
 */
class UserLocationSeeder extends Seeder
{
    // ── Real Cambodian addresses with verified coordinates ───────────────────
    private array $locations = [
        // Phnom Penh
        ['address' => 'St. 310, Boeng Keng Kang I, Phnom Penh',       'city' => 'Phnom Penh',  'lat' => 11.547,  'lng' => 104.919],
        ['address' => 'Norodom Blvd, Chamkarmon, Phnom Penh',         'city' => 'Phnom Penh',  'lat' => 11.556,  'lng' => 104.928],
        ['address' => 'Monivong Blvd, 7 Makara, Phnom Penh',          'city' => 'Phnom Penh',  'lat' => 11.565,  'lng' => 104.913],
        ['address' => 'Russian Federation Blvd, Toul Kork, Phnom Penh','city' => 'Phnom Penh', 'lat' => 11.574,  'lng' => 104.896],
        ['address' => 'St. 2004, Chroy Changvar, Phnom Penh',         'city' => 'Phnom Penh',  'lat' => 11.601,  'lng' => 104.930],
        ['address' => 'St. 271, Tuol Kork, Phnom Penh',               'city' => 'Phnom Penh',  'lat' => 11.562,  'lng' => 104.878],
        ['address' => 'Kampuchea Krom Blvd, Meanchey, Phnom Penh',    'city' => 'Phnom Penh',  'lat' => 11.539,  'lng' => 104.945],
        ['address' => 'St. 1003, Dangkao, Phnom Penh',                'city' => 'Phnom Penh',  'lat' => 11.509,  'lng' => 104.889],
        ['address' => 'St. 163, Toul Svay Prey, Phnom Penh',          'city' => 'Phnom Penh',  'lat' => 11.546,  'lng' => 104.908],
        // Siem Reap
        ['address' => 'Pub Street, Siem Reap',                        'city' => 'Siem Reap',   'lat' => 13.362,  'lng' => 103.859],
        ['address' => 'Sivatha Blvd, Siem Reap',                      'city' => 'Siem Reap',   'lat' => 13.358,  'lng' => 103.853],
        ['address' => 'Airport Road, Siem Reap',                      'city' => 'Siem Reap',   'lat' => 13.369,  'lng' => 103.843],
        // Battambang
        ['address' => 'Riverside Road, Battambang',                   'city' => 'Battambang',  'lat' => 13.095,  'lng' => 103.196],
        ['address' => 'Battambang Market',                            'city' => 'Battambang',  'lat' => 13.105,  'lng' => 103.200],
        // Sihanoukville
        ['address' => 'Ekareach St, Sihanoukville',                   'city' => 'Sihanoukville', 'lat' => 10.632, 'lng' => 103.523],
        ['address' => 'Otres Beach Road, Sihanoukville',              'city' => 'Sihanoukville', 'lat' => 10.605, 'lng' => 103.527],
        // Kampong Cham
        ['address' => 'Kampong Cham Riverside',                       'city' => 'Kampong Cham', 'lat' => 11.998, 'lng' => 105.465],
        // Kampot
        ['address' => 'Old Market Area, Kampot',                      'city' => 'Kampot',      'lat' => 10.611,  'lng' => 104.179],
        // Kratie
        ['address' => 'Kratie Riverfront, Kratie',                    'city' => 'Kratie',      'lat' => 12.481,  'lng' => 106.019],
        // Takeo
        ['address' => 'Central Market, Takeo',                        'city' => 'Takeo',       'lat' => 10.983,  'lng' => 104.783],
        // Kampong Speu
        ['address' => 'Kampong Speu Town Center',                     'city' => 'Kampong Speu', 'lat' => 11.453, 'lng' => 104.521],
        // Pursat
        ['address' => 'Pursat Market Road, Pursat',                   'city' => 'Pursat',      'lat' => 12.538,  'lng' => 103.919],
        // Prey Veng
        ['address' => 'Prey Veng Town Center',                        'city' => 'Prey Veng',   'lat' => 11.484,  'lng' => 105.324],
        // Svay Rieng
        ['address' => 'Svay Rieng Market',                            'city' => 'Svay Rieng',  'lat' => 11.082,  'lng' => 105.799],
        // Banteay Meanchey
        ['address' => 'Poipet Town Center, Banteay Meanchey',         'city' => 'Banteay Meanchey', 'lat' => 13.656, 'lng' => 102.562],
    ];

    private array $labels = ['Home', 'Office', 'Work'];

    public function run(): void
    {
        if (User::count() === 0) {
            $this->command->warn('⚠️  UserLocationSeeder: no users yet — run UserSeeder first.');
            return;
        }

        // Clean slate: re-seeding should produce a consistent location set.
        UserLocation::query()->delete();

        $created = 0;
        foreach (User::all() as $user) {
            $count = rand(1, 3);

            for ($i = 0; $i < $count; $i++) {
                $loc = $this->locations[array_rand($this->locations)];

                UserLocation::create([
                    'user_id'    => $user->id,
                    'name'       => $this->labels[$i % count($this->labels)],
                    'phone'      => $this->phone(),
                    'address'    => $loc['address'],
                    'city'       => $loc['city'],
                    'country'    => 'Cambodia',
                    'note'       => $i === 0 ? null : (rand(0, 1) ? 'Call before delivery' : null),
                    'is_default' => $i === 0,          // first location = default
                    'lat'        => $loc['lat'] + (rand(-40, 40) / 1000),
                    'lng'        => $loc['lng'] + (rand(-40, 40) / 1000),
                    'map_address'=> $loc['address'],
                ]);
                $created++;
            }
        }

        $this->command->info("✅ UserLocationSeeder: {$created} Cambodia locations across " . User::count() . ' users.');
    }

    private function phone(): string
    {
        $prefixes = ['010', '011', '012', '015', '016', '017', '018', '061', '066', '067',
                     '068', '069', '070', '076', '077', '078', '081', '084', '085', '086',
                     '087', '088', '089', '090', '092', '093', '095', '096', '097', '098', '099'];
        $num = str_pad((string) rand(0, 9999999), 7, '0', STR_PAD_LEFT);

        return $prefixes[array_rand($prefixes)] . ' ' . substr($num, 0, 3) . ' ' . substr($num, 3);
    }
}