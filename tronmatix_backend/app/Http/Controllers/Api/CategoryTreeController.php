<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryTreeController extends Controller
{
    /**
     * GET /api/categories/tree
     * Public, no auth. Returns the full 4-level navigation tree
     * (Category → MainCategory → SubCategory → Brand), each level
     * filtered to active records and sorted by the `order` column.
     */
    public function tree(): JsonResponse
    {
        $categories = Category::active()->orderBy('order')->with([
            'mainCategories'      => fn ($q) => $q->active()->orderBy('order'),
            'mainCategories.subCategories'      => fn ($q) => $q->active()->orderBy('order'),
            'mainCategories.subCategories.brands' => fn ($q) => $q->active()->orderBy('order'),
        ])->get()->toArray();

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }
}
