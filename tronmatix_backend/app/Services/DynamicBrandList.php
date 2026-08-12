<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Support\Collection;

/**
 * Returns the full brand catalog from the Brand model tree.
 *
 * Brands and categories are completely separate systems:
 *  • Brands are managed on the dashboard Brands page (Level 4 under Category → SubCategory).
 *  • The product form pulls from this list — no free-text brand pollution from products.
 *
 * Used by:
 *  • the dashboard product form (brand datalist / select)
 *  • the API endpoint for storefront brand filters
 */
class DynamicBrandList
{
    /**
     * @return Collection<int, array{id: int, name: string, slug: string}> sorted by `order`
     */
    public static function all(): Collection
    {
        return Brand::active()
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn ($b) => [
                'id'   => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
            ])
            ->values();
    }
}
