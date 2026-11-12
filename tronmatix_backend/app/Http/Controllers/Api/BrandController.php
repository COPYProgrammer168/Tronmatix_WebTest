<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    /**
     * GET /api/brands
     * Returns all active brands for the storefront brand marquee.
     * Sorted by `order` ascending, then `id`.
     *
     * Response:
     * {
     *   "success": true,
     *   "data": [
     *     { "id": 1, "name": "AMD", "slug": "amd", "image": "https://...", "order": 0 },
     *     ...
     *   ]
     * }
     */
    /**
     * GET /api/brands/product-list
     * Returns a flat array of distinct brand strings actually used by
     * products in the catalog — sorted A-Z.
     *
     * Response:
     * { "success": true, "data": ["AMD", "INTEL", "NVIDIA", ...] }
     */
    public function productBrands(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => \App\Services\DynamicBrandList::all()->values(),
        ]);
    }

    public function index(): JsonResponse
    {
        // Fetch all active brands, then deduplicate by name.
        // When the same brand appears under multiple sub-categories we keep
        // the one with the lowest order (the seeder assigns clean brands
        // order 0-34; older duplicates have higher order values).
        $all = Brand::active()
            ->orderBy('order')
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'image', 'order']);

        $seen = [];
        $data = [];

        foreach ($all as $b) {
            $key = strtoupper(trim($b->name));
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $image = $b->image;
            if ($image && ! str_starts_with($image, 'http')) {
                $image = rtrim((string) env('APP_URL', config('app.url', 'http://localhost')), '/') . '/' . ltrim($image, '/');
            }

            $data[] = [
                'id'    => $b->id,
                'name'  => $b->name,
                'slug'  => $b->slug,
                'image' => $image,
                'order' => $b->order,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
