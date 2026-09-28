<?php

// database/seeders/BrandSeeder.php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeder.
     *
     * Each brand is seeded once (no duplicates).
     *
     * sub_category_id maps to the brand's REAL category (AMD → "AMD ALL
     * SERIES" under CPU, NVIDIA → VGA, …) so the admin brand list and the
     * category tree line up. Falls back to NULL when the sub-category is
     * missing locally — sub_category_id is nullable and the storefront
     * marquee ignores it (it shows all active brands flat).
     *
     * Logo image: the marquee renders a text fallback (brand name) whenever
     * an image is broken or blank, so a missing logo never blanks the row.
     */
    public function run(): void
    {
        $brands = [
            // name => [sub_category name (or null), image URL (brand logo, nullable)]
            ['AMD',             ['AMD ALL SERIES',     'https://tronmatixcomputer.com/images/1737292852.png']],
            ['ARCTIC',          ['COOLER',             'https://tronmatixcomputer.com/images/1739434640.jpg']],
            ['ASROCK',          ['B850 SERIES',        'https://tronmatixcomputer.com/images/1746179238.jpg']],
            ['ASUS',            ['Z790 SERIES',        'https://tronmatixcomputer.com/images/1739169199.jpg']],
            ['COOLER MASTER',   ['COOLER',             'https://tronmatixcomputer.com/images/1741326079.jpg']],
            ['CORSAIR',         ['16GB DDR5',          'https://tronmatixcomputer.com/images/1774926337.jpg']],
            ['CRUCIAL',         ['32GB DDR5',          'https://tronmatixcomputer.com/images/1740986421.jpg']],
            ['DEEPCOOL',        ['COOLER',             'https://tronmatixcomputer.com/images/1739344217.jpg']],
            ['DX RACER',        [null,                 null]],
            ['FANTECH',         ['MOUSE',              'https://tronmatixcomputer.com/images/1746513418.jpg']],
            ['FRACTAL DESIGN',  ['CASE',               'https://tronmatixcomputer.com/images/1741060263.jpg']],
            ['GIGABYTE',        ['B760 SERIES',        'https://tronmatixcomputer.com/images/1739169607.png']],
            ['G.SKILL',         ['32GB DDR5',          'https://tronmatixcomputer.com/images/1774927168.png']],
            ['INTEL',           ['INTEL 13TH',         'https://tronmatixcomputer.com/images/1737085653.jpg']],
            ['KINGSTON',        ['16GB DDR5',          'https://tronmatixcomputer.com/images/1746168211.jpg']],
            ['LG',              ['27INCH',             'https://tronmatixcomputer.com/images/1775100841.jpg']],
            ['LIAN LI',         ['CASE',               'https://tronmatixcomputer.com/images/1740980790.jpg']],
            ['LOGITECH',        ['MOUSE',              'https://tronmatixcomputer.com/images/1752722015.jpg']],
            ['MSI',             ['Z790 SERIES',        'https://tronmatixcomputer.com/images/1737608278.jpg']],
            ['NOCTUA',          ['COOLER',             'https://tronmatixcomputer.com/images/1739344143.jpg']],
            ['NVIDIA',          ['VGA AMD ALL SERIES', 'https://tronmatixcomputer.com/images/1739169607.png']],
            ['NZXT',            ['LIQUID 360MM',       'https://tronmatixcomputer.com/images/1740981002.jpg']],
            ['PNY',             ['RTX 5060',           'https://tronmatixcomputer.com/images/1738574770.jpg']],
            ['RAZER',           ['MOUSE',              'https://tronmatixcomputer.com/images/1752722131.jpg']],
            ['SAMSUNG',         ['27INCH',             'https://tronmatixcomputer.com/images/1775097361.jpg']],
            ['SECRETLAB',       [null,                 null]],
            ['SEASONIC',        ['650W',               'https://tronmatixcomputer.com/images/1741326079.jpg']],
            ['SAPPHIRE',        ['VGA AMD ALL SERIES', 'https://tronmatixcomputer.com/images/1747364493.jpg']],
            ['STEELSERIES',     [null,                 'https://tronmatixcomputer.com/images/1752724848.jpg']],
            ['THERMALRIGHT',    ['COOLER',             'https://tronmatixcomputer.com/images/1757654409.jfif']],
            ['TTR RACING',      [null,                 null]],
            ['WD',              ['1TB',                'https://tronmatixcomputer.com/images/1740986025.jpg']],
            ['XPG',             ['1TB',                'https://tronmatixcomputer.com/images/1737294513.png']],
            ['ZOTAC',           ['RTX 5060',           'https://tronmatixcomputer.com/images/1746178549.jpg']],
        ];

        $orderGlobal = 0;

        foreach ($brands as [$name, [$subName, $image]]) {
            // Skip if a brand with this name already exists (idempotent re-run)
            if (Brand::where('name', $name)->exists()) continue;

            // Resolve the sub-category id by name where available; NULL otherwise.
            $subCategoryId = $subName
                ? \App\Models\SubCategory::where('name', $subName)->value('id')
                : null;

            Brand::create([
                'sub_category_id' => $subCategoryId,
                'name'            => $name,
                'image'           => $image,
                'order'           => $orderGlobal,
                'is_active'       => true,
            ]);

            $orderGlobal++;
        }
    }
}
