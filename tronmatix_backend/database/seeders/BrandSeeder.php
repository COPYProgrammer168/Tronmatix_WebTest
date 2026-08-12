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
     * Logo = first product image found in the live catalog for that brand.
     * All brands assigned to sub_category_id 572 (TABLE / CHAIR) — the
     * sub-category is only used for admin tree navigation; the storefront
     * marquee fetches all active brands flat via /api/brands.
     */
    public function run(): void
    {
        $brands = [
            // name => image URL (first product image for that brand from catalog)
            ['AMD',             'https://tronmatixcomputer.com/images/1737292852.png'],
            ['ARCTIC',          'https://tronmatixcomputer.com/images/1739434640.jpg'],
            ['ASROCK',          'https://tronmatixcomputer.com/images/1746179238.jpg'],
            ['ASUS',            'https://tronmatixcomputer.com/images/1739169199.jpg'],
            ['COOLER MASTER',   'https://tronmatixcomputer.com/images/1741326079.jpg'],
            ['CORSAIR',         'https://tronmatixcomputer.com/images/1774926337.jpg'],
            ['CRUCIAL',         'https://tronmatixcomputer.com/images/1740986421.jpg'],
            ['DEEPCOOL',        'https://tronmatixcomputer.com/images/1739344217.jpg'],
            ['DX RACER',        'https://tronmatixcomputer.com/images/1746434219.pn'],
            ['FANTECH',         'https://tronmatixcomputer.com/images/1746513418.jpg'],
            ['FRACTAL DESIGN',  'https://tronmatixcomputer.com/images/1741060263.jpg'],
            ['GIGABYTE',        'https://tronmatixcomputer.com/images/1739169607.png'],
            ['G.SKILL',         'https://tronmatixcomputer.com/images/1774927168.pn'],
            ['INTEL',           'https://tronmatixcomputer.com/images/1737085653.jpg'],
            ['KINGSTON',        'https://tronmatixcomputer.com/images/1746168211.jpg'],
            ['LG',              'https://tronmatixcomputer.com/images/1775100841.jpg'],
            ['LIAN LI',         'https://tronmatixcomputer.com/images/1740980790.jpg'],
            ['LOGITECH',        'https://tronmatixcomputer.com/images/1752722015.jpg'],
            ['MSI',             'https://tronmatixcomputer.com/images/1737608278.jpg'],
            ['NOCTUA',          'https://tronmatixcomputer.com/images/1739344143.jpg'],
            ['NVIDIA',          'https://tronmatixcomputer.com/images/1739169607.png'],
            ['NZXT',            'https://tronmatixcomputer.com/images/1740981002.jpg'],
            ['PNY',             'https://tronmatixcomputer.com/images/1738574770.jpg'],
            ['RAZER',           'https://tronmatixcomputer.com/images/1752722131.jpg'],
            ['SAMSUNG',         'https://tronmatixcomputer.com/images/1775097361.jpg'],
            ['SECRETLAB',       'https://tronmatixcomputer.com/images/1746435663.jpg'],
            ['SEASONIC',        'https://tronmatixcomputer.com/images/1741326079.jpg'],
            ['SAPPHIRE',        'https://tronmatixcomputer.com/images/1747364493.jpg'],
            ['STEELSERIES',     'https://tronmatixcomputer.com/images/1752724848.jpg'],
            ['THERMALRIGHT',    'https://tronmatixcomputer.com/images/1757654409.jfif'],
            ['TTR RACING',      'https://tronmatixcomputer.com/images/1746510916.jpg'],
            ['WD',              'https://tronmatixcomputer.com/images/1740986025.jpg'],
            ['XPG',             'https://tronmatixcomputer.com/images/1737294513.pn'],
            ['ZOTAC',           'https://tronmatixcomputer.com/images/1746178549.jpg'],
        ];

        $orderGlobal = 0;

        foreach ($brands as [$name, $image]) {
            // Skip if a brand with this name already exists (idempotent re-run)
            if (Brand::where('name', $name)->exists()) continue;

            Brand::create([
                'sub_category_id' => 572,
                'name'            => $name,
                'slug'            => strtolower(str_replace(' ', '-', $name)),
                'image'           => $image,
                'order'           => $orderGlobal,
                'is_active'       => true,
            ]);

            $orderGlobal++;
        }
    }
}
