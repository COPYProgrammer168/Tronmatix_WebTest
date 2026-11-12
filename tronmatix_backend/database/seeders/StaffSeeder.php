<?php

// database/seeders/StaffSeeder.php

namespace Database\Seeders;

use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds 10 staff accounts with authentic Khmer names.
 *
 * Role mix: developers portal user, sellers/editors/delivery for the staff
 * portal. All share one default password below for demo logins.
 */
class StaffSeeder extends Seeder
{
    // ── Authentic Khmer given names (ខ្មែរ) ────────────────────────────────────
    private array $khmerNames = [
        'សុខា', 'ដារ៉ា', 'ម៉ាលី', 'វីរៈ', 'សីហា', 'កណិក្កា', 'សុភល', 'ចន្ទ្រា',
        'រតនា', 'សុទ្ធា', 'ពិសិដ្ឋ', 'នីតា', 'រដ្ឋា', 'ភល្លា', 'សារុន', 'ទីណា',
        'សុវណ្ណា', 'គឹមហេង', 'មុនី', 'ស្រីមុំ', 'វាណា', 'នរិទ្ធ', 'សុខួរ', 'មករា',
        'បូរ៉ា', 'ច័ន្ទ', 'វុទ្ធី', 'សុភ័គ', 'សំណាង', 'ចេនដា',
    ];

    private array $roles = ['editor', 'seller', 'delivery', 'seller', 'editor', 'delivery', 'developer', 'seller', 'editor', 'delivery'];

    public function run(): void
    {
        if (Staff::count() > 0) {
            $this->command->warn('⚠️  Staff already exist — skipping (re-seed once fresh).');
            return;
        }

        $now = Carbon::now();
        $rows = [];

        // First staff member is the developer/owner (full name).
        $slots = 10;
        for ($i = 0; $i < $slots; $i++) {
            $given = $this->khmerNames[array_rand($this->khmerNames)];

            $name = $i === 0
                ? $given . ' ឆាយ'                                   // e.g. "សុខា ឆាយ"
                : $given;                                            // single Khmer given name

            $rows[] = [
                'name'           => $name,
                'email'          => 'staff' . ($i + 1) . '@tronmatix.com',
                'username'       => 'staff' . ($i + 1),
                'password'       => Hash::make('Staff@1234'),
                'avatar'         => null,
                'role'           => $this->roles[$i],
                'is_active'      => true,
                'online_status'  => 'offline',
                'last_login_at'  => $i === 0 ? $now : null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        Staff::insert($rows);

        $this->command->info('✅ StaffSeeder:  ' . count($rows) . ' staff with Khmer names.');
        $this->command->info('   Default password: Staff@1234 (all)');
    }
}