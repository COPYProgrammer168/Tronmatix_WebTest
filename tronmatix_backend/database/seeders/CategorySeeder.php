<?php

// database/seeders/CategorySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds the full 4-level navigation tree (Category → MainCategory → SubCategory
 * → Brand) to match the live storefront at https://tronmatixcomputer.com exactly.
 *
 * The tree below was captured directly from the real site's navigation menu:
 *
 *   PC BUILD      → UNDER 1K, UNDER 2K, UNDER 3K, UNDER 4K, UNDER 5K, 5K UP
 *   MONITOR       → 25/27/32/34/39/42/45/48/49 INCH
 *   PC PARTS      → CPU, RAM, MAINBOARD, COOLING, M2, VGA, CASE, POWER SUPPLY, FAN
 *   HOT ITEM      → BEST PRICE
 *   ACCESSORY     → KEYBOARD, MOUSE, HEADSET, EARPHONE, MONITOR STAND, SPEAKER,
 *                   MICROPHONE, WEBCAM, MOUSEPAD, LIGHTBAR, ROUTER, STRIMER SET
 *   TABLE / CHAIR → DX RACER, SECRETLAB, RAZER, CONSAIR, FANTECH,
 *                   COOLER MASTER, TTR RACING, ASUS  (as brands)
 *
 * Sub-category names under each PC PART main are listed in full below.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // ══ Rebuild the tree from scratch ════════════════════════════════════
            // updateOrInsert only inserts/updates — it never deletes stale rows
            // from older seeder versions (e.g. old "PC BUILD UNDER 1K" main
            // categories, "BEST SET", duplicate "256G" vs "256GB"). Clearing the
            // four navigation tables first guarantees the seeded tree matches the
            // live site exactly every run. FK cascades clear the children.
            DB::table('brands')->delete();
            DB::table('sub_categories')->delete();
            DB::table('main_categories')->delete();
            DB::table('categories')->delete();

            // ══ Level 1: Categories (top-level nav) ════════════════════════════
            $pcBuildCat    = $this->seedCategory('PC BUILD', 1);
            $monitorCat    = $this->seedCategory('MONITOR', 2);
            $pcPartCat     = $this->seedCategory('PC PARTS', 3);
            $hotItemCat    = $this->seedCategory('HOT ITEM', 4);
            $accessoryCat  = $this->seedCategory('ACCESSORY', 5);
            $tableChairCat = $this->seedCategory('TABLE / CHAIR', 6);

            // ══ Level 2: Main Categories ═══════════════════════════════════════
            $pcBuildMc   = $this->seedMainCategory($pcBuildCat->id, 'PC BUILD', 1);
            $monitorMc   = $this->seedMainCategory($monitorCat->id, 'MONITOR', 1);

            $pcPartMainCates = [];
            foreach (['CPU','RAM','MAINBOARD','COOLING','M2','VGA','CASE','POWER SUPPLY','FAN'] as $i => $name) {
                $pcPartMainCates[$name] = $this->seedMainCategory($pcPartCat->id, $name, $i + 1);
            }

            $hotItemMc   = $this->seedMainCategory($hotItemCat->id, 'HOT ITEM', 1);
            $accessoryMc = $this->seedMainCategory($accessoryCat->id, 'ACCESSORY', 1);
            $tableChairMc = $this->seedMainCategory($tableChairCat->id, 'TABLE / CHAIR', 1);

            // ══ Level 3: Sub Categories ════════════════════════════════════════

            // PC BUILD — budget ranges (from real site)
            foreach (['UNDER 1K','UNDER 2K','UNDER 3K','UNDER 4K','UNDER 5K','5K UP'] as $i => $name) {
                $this->seedSubCategory($pcBuildMc->id, $name, $i + 1);
            }

            // MONITOR — screen sizes (real site includes 45INCH)
            foreach (['25INCH','27INCH','32INCH','34INCH','39INCH','42INCH','45INCH','48INCH','49INCH'] as $i => $name) {
                $this->seedSubCategory($monitorMc->id, $name, $i + 1);
            }

            // PC PARTS — sub-categories per main part (from real site)
            $pcPartSubCateMap = [
                'CPU'          => ['INTEL 12TH','INTEL 13TH','INTEL 14TH','INTEL 15TH ULTRA','AMD ALL SERIES'],
                'RAM'          => ['8GB DDR4','16GB DDR4','16GB DDR5','32GB DDR5','24GB DDR5','48GB DDR5','96GB DDR5','RAM DDR5 64GB X2 128GB'],
                'MAINBOARD'    => ['H610 SERIES','B760 SERIES','Z790 SERIES','Z890 SERIES','X670 SERIES','X870 SERIES','B850 SERIES','H810 SERIES','B860 SERIES'],
                'COOLING'      => ['THERMAL GREASE','COOLER','LIQUID 240MM','LIQUID 360MM','LIQUID WATERLOOP'],
                'M2'           => ['256GB','500GB','1TB','2TB','4TB','8TB','ENCLOSURE','M.2 TRAY'],
                'VGA'          => ['RTX3050','RTX5080','RTX5090','RTX 5070TI','INTEL VGA','VGA AMD ALL SERIES','VGA RTX5070','RTX5060TI','RTX 5060'],
                'CASE'         => ['UNDER 50$','UNDER 100$','UNDER 200$','UNDER 300$','UNDER 500$','UNDER 1000$','UNDER 10000$','MINI ITX'],
                'POWER SUPPLY' => ['550W','650W','750W','850W','1000W','1200W','1600W','2200W','3000W'],
                'FAN'          => [],   // FAN has no sub-categories on the real site
            ];

            foreach ($pcPartSubCateMap as $mainName => $subNames) {
                $mc = $pcPartMainCates[$mainName];
                foreach ($subNames as $i => $subName) {
                    $this->seedSubCategory($mc->id, $subName, $i + 1);
                }
            }

            // HOT ITEM — single sub-category (real site: BEST PRICE only)
            foreach (['BEST PRICE'] as $i => $name) {
                $this->seedSubCategory($hotItemMc->id, $name, $i + 1);
            }

            // ACCESSORY — includes STRIMER SET (real site)
            foreach (['KEYBOARD','MOUSE','HEADSET','EARPHONE','MONITOR STAND','SPEAKER','MICROPHONE','WEBCAM','MOUSEPAD','LIGHTBAR','ROUTER','STRIMER SET'] as $i => $name) {
                $this->seedSubCategory($accessoryMc->id, $name, $i + 1);
            }

            // TABLE / CHAIR — brands are the sub-level (real site lists brands directly)
            $tableChairSc = $this->seedSubCategory($tableChairMc->id, 'GENERAL', 1);

            // ══ Level 4: Brands ════════════════════════════════════════════════

            // TABLE / CHAIR brands (real names from real site)
            $realBrands = ['DX RACER','SECRETLAB','RAZER','CONSAIR','FANTECH','COOLER MASTER','TTR RACING','ASUS'];
            foreach ($realBrands as $i => $name) {
                $this->seedBrand($tableChairSc->id, $name, $i + 1);
            }
        });
    }

    private function seedCategory(string $name, int $order)
    {
        DB::table('categories')->updateOrInsert(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'order' => $order, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        return DB::table('categories')->where('slug', Str::slug($name))->first();
    }

    private function seedMainCategory(int $categoryId, string $name, int $order)
    {
        DB::table('main_categories')->updateOrInsert(
            ['slug' => Str::slug($name)],
            ['category_id' => $categoryId, 'name' => $name, 'order' => $order, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        return DB::table('main_categories')->where('slug', Str::slug($name))->first();
    }

    private function seedSubCategory(int $mainCategoryId, string $name, int $order)
    {
        DB::table('sub_categories')->updateOrInsert(
            ['slug' => Str::slug($name)],
            ['main_category_id' => $mainCategoryId, 'name' => $name, 'order' => $order, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        return DB::table('sub_categories')->where('slug', Str::slug($name))->first();
    }

    private function seedBrand(int $subCategoryId, string $name, int $order)
    {
        $baseSlug = Str::slug($name) ?: 'brand';
        $slug = $baseSlug;
        $i = 1;
        while (DB::table('brands')->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . ++$i;
        }

        DB::table('brands')->updateOrInsert(
            ['slug' => $slug],
            ['sub_category_id' => $subCategoryId, 'name' => $name, 'order' => $order, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        return DB::table('brands')->where('slug', $slug)->first();
    }
}