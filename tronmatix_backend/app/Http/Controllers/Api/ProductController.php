<?php

// app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * GET /api/products
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // 'all' is a special slug meaning no category filter — show everything
        // Also support category[] array param (sent by HomePage for multi-sub categories like MONITOR)
        if ($request->has('category') && is_array($request->input('category'))) {
            $cats = array_values(array_filter(array_map('strtolower', $request->input('category'))));
            if (count($cats) > 0) {
                $query->whereIn(DB::raw('LOWER(category)'), $cats);
            }
        } elseif ($request->filled('category') && strtolower($request->category) !== 'all') {
            $query->whereRaw('LOWER(category) = ?', [strtolower($request->category)]);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->filled('search')) {
            $term = '%'.strtolower($request->search).'%';
            $query->where(fn ($q) => $q
                ->whereRaw('LOWER(name) LIKE ?', [$term])
                ->orWhereRaw('LOWER(category) LIKE ?', [$term])
                ->orWhereRaw('LOWER(brand) LIKE ?', [$term])
                ->orWhereRaw('LOWER(description) LIKE ?', [$term])
            );
        }

        // Multi-category filter: ?cats=CPU,RAM,MAINBOARD (used by CategoryPage)
        if ($request->filled('cats')) {
            $cats = array_map('strtolower', array_filter(explode(',', $request->cats)));
            if (count($cats) > 0) {
                $query->whereIn(DB::raw('LOWER(category)'), $cats);
            }
        }

        match ($request->input('sort', 'default')) {
            'price-asc'  => $query->orderBy('price', 'asc'),
            'price-desc' => $query->orderBy('price', 'desc'),
            'name'       => $query->orderBy('name', 'asc'),
            'rating'     => $query->orderBy('rating', 'desc'),
            'newest'     => $query->latest(),   // used by HomePage new products section
            default      => $query->latest(),
        };

        $perPage = min((int) $request->input('per_page', 12), 999);
        $products = $query->paginate($perPage);

        // use model's getAllImagesAttribute() (appended) instead of re-parsing JSON manually
        return response()->json([
            'data' => $products->items(),  // all_images is appended — no extra mapping needed
            'total' => $products->total(),
            'lastPage' => $products->lastPage(),
            'page' => $products->currentPage(),
        ]);
    }

    /**
     * GET /api/products/{id}
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);

        //include all columns needed by ProductCard / ProductDetail
        $related = Product::where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(8)
            ->get(); //no select() restriction — model appends all_images automatically

        return response()->json([
            'data' => $product,    // all_images, in_stock, display_price appended by model
            'related' => $related,
        ]);
    }
}