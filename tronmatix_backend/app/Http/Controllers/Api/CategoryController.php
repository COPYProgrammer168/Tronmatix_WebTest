<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * All navigation categories with their sub-items.
     * In a real app you'd store these in a DB table —
     * this static list matches the frontend NAV_CATEGORIES exactly.
     */
    private array $categories = [
        [
            'label' => 'NEW ADD',
            'slug' => 'new-add',
            'items' => [
                'New Arrival',
            ],
        ],
        [
            'label' => 'PC BUILD',
            'slug' => 'pc-build',
            'items' => [
                'PC BUILD UNDER 1K',
                'PC BUILD UNDER 2K',
                'PC BUILD UNDER 3K',
                'PC BUILD UNDER 4K',
                'PC BUILD UNDER 5K',
                'PC BUILD 5K UP',
            ],
        ],
        [
            'label' => 'MONITOR',
            'slug' => 'monitor',
            'items' => [
                'MONITOR 25INCH',
                'MONITOR 27INCH',
                'MONITOR 32INCH',
                'MONITOR 34INCH',
                'MONITOR 39INCH',
                'MONITOR 42INCH',
                'MONITOR 48INCH',
                'MONITOR 49INCH',
            ],
        ],
        [
            'label' => 'PC PART',
            'slug' => 'pc-part',
            'items' => [
                'CPU',
                'RAM',
                'MAINBOARD',
                'COOLING',
                'M2',
                'VGA',
                'CASE',
                'POWER SUPPLY',
                'FAN',
            ],
        ],
        [
            'label' => 'HOT ITEM',
            'slug' => 'hot-item',
            'items' => [
                'BEST PRICE',
                'BEST SET',
            ],
        ],
        [
            'label' => 'ACCESSORY',
            'slug' => 'accessory',
            'items' => [
                'KEYBOARD',
                'MOUSE',
                'HEADSET',
                'EARPHONE',
                'MONITOR STAND',
                'SPEAKER',
                'MICROPHONE',
                'WEBCAM',
                'MOUSEPAD',
                'LIGHTBAR',
                'ROUTER',
            ],
        ],
        [
            'label' => 'TABLE & CHAIR',
            'slug' => 'table-chair',
            'items' => [
                'DX RACER',
                'SECRETLAB',
                'RAZER',
                'CONSAIR',
                'FANTECH',
                'COOLER MASTER',
                'TTR RACING',
            ],
        ],
    ];

    /**
     * GET /api/categories
     * Returns all categories with their sub-items.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->categories,
        ]);
    }

    /**
     * GET /api/categories/{slug}
     * Returns a single category by slug with its sub-items.
     */
    public function show(string $slug): JsonResponse
    {
        $category = collect($this->categories)
            ->firstWhere('slug', $slug);

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }
}
