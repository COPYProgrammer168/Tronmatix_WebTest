<?php

// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds 100 VIP customer accounts spread across the last ~18 months.
 *
 * Every account is role=vip per the new system spec. Emails/phones are unique,
 * login activity is staggered so the dashboard "recently logged in" panel has
 * fresh data, and delivery locations are created by UserLocationSeeder.
 */
class UserSeeder extends Seeder
{
    private array $firstNames = [
        'Sokha', 'Dara', 'Chanthy', 'Visal', 'Sreymom', 'Bopha', 'Rathana', 'Kimheng',
        'Sophal', 'Makara', 'Chan', 'Vanna', 'Samnang', 'Sopheak', 'Chenda', 'Navy',
        'Borey', 'Phalla', 'Sarom', 'Thida', 'Khemara', 'Chantha', 'Rithy', 'Sothea',
        'Sreyleak', 'Pisey', 'Rattanak', 'Mony', 'Davin', 'Sreyneang', 'Phearom',
        'Tola', 'Chanmony', 'Sreypov', 'Kunthea', 'Sopheap', 'Chhun', 'Sokun',
        'Meng', 'Sreyroth', 'Heng', 'Nara', 'Phin', 'Thira', 'Leap', 'Vichet',
        'Sokuntheary', 'Chamnan', 'Sreynith', 'Ravy', 'Dina', 'Cheat', 'Kimleng',
        'Sophea', 'Kosal', 'Virak', 'Sreyla', 'Piseth', 'Lida', 'Bunna', 'Sovann',
        'Ratha', 'Kimhak', 'Veasna', 'Narith', 'Channary', 'Sreychea', 'Dyna',
        'Soknita', 'Pheakdey', 'Seiha', 'Malyn', 'Viseth', 'Sopheng', 'Chantrea',
        'Radom', 'Sokhom', 'Vithy', 'Sirey', 'Theary', 'Sinath', 'Botum', 'Chakra',
        'Soriya', 'Panha', 'Daro', 'Sambath', 'Nita', 'Reya', 'Kanha', 'Sophea Chea',
        'Vibol', 'Sokly', 'Chheang', 'Pheary', 'Monorom', 'Sovichea',
    ];

    private array $lastNames = [
        'Chea', 'Ly', 'Kim', 'Peng', 'Noun', 'Sann', 'Hok', 'Tep', 'Sao', 'Meas',
        'Oum', 'Yim', 'Nuon', 'Keo', 'Chhoeun', 'Chan', 'Seng', 'Kuy', 'Hour', 'Chhim',
        'Bun', 'In', 'Yin', 'Lim', 'Kong', 'Heng', 'Phorn', 'Rith', 'Soeun', 'Lorn',
        'Sok', 'Pov', 'Ros', 'Men', 'Phal', 'Nget', 'Touch', 'Vong', 'Em', 'Soth',
    ];

    private array $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com'];

    public function run(): void
    {
        $created = 0;

        for ($i = 0; $i < 100; $i++) {
            $firstName = $this->firstNames[$i % count($this->firstNames)];
            $lastName  = $this->lastNames[array_rand($this->lastNames)];
            $name      = trim($firstName . ' ' . $lastName);

            // username/email: Unicode names are used verbatim in `name`; the
            // ASCII slug of the given name keeps login handles predictable.
            $username = strtolower(preg_replace('/[^a-z]/', '', $firstName) . $lastName[0]) . rand(10, 99);
            $email    = $username . '@' . $this->domains[array_rand($this->domains)];

            // Created across the last ~18 months; most within the last 6.
            $createdAt = Carbon::now()->subMonths(rand(0, 18))->subDays(rand(0, 28));

            // Login activity: ~60% active this week, ~25% this month, rest dormant.
            $lastLoginRoll = rand(1, 100);
            if ($lastLoginRoll <= 60) {
                $lastLogin = Carbon::now()->subDays(rand(0, 7));
            } elseif ($lastLoginRoll <= 85) {
                $lastLogin = Carbon::now()->subDays(rand(8, 30));
            } else {
                $lastLogin = Carbon::now()->subDays(rand(31, 60));
            }

            // ~30% also logged in today/yesterday — feeds the dashboard list.
            $recentLogin = (rand(0, 2) === 0)
                ? Carbon::now()->subDays(rand(0, 2))->subHours(rand(0, 23))
                : null;

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'username'       => $username,
                    'name'           => $name,
                    'password'       => Hash::make('Password@123'),
                    'phone'          => $this->phone(),
                    'role'           => 'vip',
                    'is_banned'      => false,
                    'email_verified_at' => $createdAt,
                    'created_at'     => $createdAt,
                    'updated_at'     => $createdAt,
                    'last_login_at'  => $lastLogin,
                    'recent_login_at' => $recentLogin,
                ]
            );

            if ($user->wasRecentlyCreated) {
                $created++;
            }
        }

        $this->command->info("✅ UserSeeder: {$created} VIP users created (total " . User::count() . ').');
        $this->command->info('   Default password: Password@123 (all accounts)');
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