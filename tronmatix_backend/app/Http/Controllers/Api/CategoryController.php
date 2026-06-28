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
                ['label' => 'CPU', 'brands' => ['INTEL 12TH','INTEL 13TH','INTEL 14TH','INTEL 15TH ULTRA','AMD ALL SERIES']],
                ['label' => 'RAM', 'brands' => ['8GB DDR4','16GB DDR4','16GB DDR5','32GB DDR5','24GB DDR5','48GB DDR5','96GB DDR5','RAM DDR5 64GB X2 128GB']],
                ['label' => 'MAINBOARD', 'brands' => ['H610 SERIES','B760 SERIES','Z790 SERIES','Z890 SERIES','X670 SERIES','X870 SERIES','B850 SERIES','H810 SERIES','B860 SERIES']],
                ['label' => 'COOLING', 'brands' => ['THERMAL GREASE','COOLER','LIQUID 240MM','LIQUID 360MM','LIQUID WATERLOOP']],
                ['label' => 'M2', 'brands' => ['256G','500G','1TB','2TB','4TB','8TB','4TB','ENCLOSURE','M.2 TRAY']],
                ['label' => 'VGA', 'brands' => ['RTX 3050','RTX 5080','RTX 5090','RTX 5070TI','INTER VGA','VGA AMD ALL SERIES','VGA RTX5070', 'RTX5060TI', 'RTX 5060']],
                ['label' => 'CASE', 'brands' => ['UNDER 50$','UNDER 100$','UNDER 200$','UNDER 300$','UNDER 500$','UNDER 1000$','UNDER 10000$','MINI ITX']],
                ['label' => 'POWER SUPPLY', 'brands' => ['550W','650W','750W','850W','1000W','1200W','1600W', '2200W']],
                ['label' => 'FAN', 'brands' => ['CASE FAN', 'RGB FAN', 'INDUSTRIAL FAN']],
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
