<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;

/**
 * Builds the category options used by the dashboard filter dropdowns
 * (products, banners, discounts).
 *
 * The options are derived from the navigation tree so the dropdown mirrors the
 * Category Management page. Each optgroup TITLE is a TOP-LEVEL CATEGORY name
 * and the options listed under it are that category's leaf product categories
 * (main-category and sub-category names that actually have products), e.g.:
 *
 *   ─── PC BUILD ───
 *     ▸ UNDER 1K
 *     ▸ UNDER 2K
 *   ─── MONITOR ───
 *     ▸ 25INCH
 *     ▸ 27INCH
 *   ─── PC PARTS ───
 *     ▸ CPU
 *     ▸ RAM
 *     ▸ MAINBOARD
 *
 * Grouping by the top-level category keeps multi-main categories (like PC
 * PARTS with CPU/RAM/MAINBOARD/…) as ONE optgroup instead of a separate
 * one-option optgroup per main. Only nodes that have products appear, and each
 * option's value is the real product `category` string so filtering works.
 */
class CategoryFilterOptions
{
    /**
     * @return array<int, array{label: string, options: array<int, array{value: string, label: string}>}>
     */
    public static function treeGroups(): array
    {
        $productCats = Product::query()
            ->select('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->map(fn ($c) => (string) $c)
            ->flip(); // uppercase-name => true lookup

        $groups = [];

        $categories = Category::active()->with([
            'mainCategories' => fn ($q) => $q->active()->orderBy('order')->with([
                'subCategories' => fn ($q) => $q->active()->orderBy('order')->with([
                    'brands' => fn ($q) => $q->active()->orderBy('order'),
                ]),
            ]),
        ])->get();

        foreach ($categories as $cat) {
            $catLabel = strtoupper($cat->name);
            $options  = [];
            $seen     = [];

            foreach ($cat->mainCategories as $mc) {
                $upperMc = strtoupper($mc->name);

                // The main-category name itself may be a product category
                // (e.g. "CPU", "RAM", "VGA" under PC PARTS).
                if (isset($productCats[$upperMc])) {
                    $options[] = ['value' => $mc->name, 'label' => $upperMc];
                    $seen[$upperMc] = true;
                }

                foreach ($mc->subCategories as $sc) {
                    // Match product categories to this sub-category:
                    //  • exact:      product "CPU" ↔ sub "CPU"
                    //  • prefixed:   product "MONITOR 27INCH" ↔ MAIN "MONITOR"
                    //                + sub "27INCH". The product category starts
                    //                with the main-category name, then contains
                    //                the sub name — a plain substring check would
                    //                wrongly match sub "MOUSEPAD" to product
                    //                "MOUSE".
                    $upperSub = strtoupper($sc->name);
                    $matched  = $productCats->filter(function ($v, $pcat) use ($upperMc, $upperSub) {
                        if ($pcat === $upperSub) {
                            return true;
                        }
                        if (str_starts_with($pcat, $upperMc)) {
                            return str_contains($pcat, $upperSub);
                        }
                        return false;
                    })->keys()->sort()->values()->all();

                    foreach ($matched as $pcat) {
                        if (! isset($seen[$pcat])) {
                            // Label is the sub-category name the way it reads in
                            // the tree (e.g. "27INCH", "UNDER 1K").
                            $options[] = ['value' => $pcat, 'label' => $upperSub];
                            $seen[$pcat] = true;
                        }
                    }

                    // Brand level under this sub-category.
                    foreach ($sc->brands as $brand) {
                        $upper = strtoupper($brand->name);
                        if (isset($productCats[$upper]) && ! isset($seen[$upper])) {
                            $options[] = ['value' => $brand->name, 'label' => $upper];
                            $seen[$upper] = true;
                        }
                    }
                }
            }

            if (count($options) > 0) {
                $groups[] = ['label' => $catLabel, 'options' => $options];
            }
        }

        return $groups;
    }
}
