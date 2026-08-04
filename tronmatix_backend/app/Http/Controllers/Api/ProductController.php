<?php

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
        if ($request->has('category') && is_array($request->input('category'))) {
            $cats = array_values(array_filter(array_map('strtolower', $request->input('category'))));
            if (count($cats) > 0) {
                $query->where(function($q) use ($cats) {
                    foreach ($cats as $cat) {
                        $q->orWhereRaw('LOWER(category) LIKE ?', ['%' . $cat . '%']);
                    }
                });
            }
        } elseif ($request->filled('category') && strtolower($request->category) !== 'all') {
            $query->whereRaw('LOWER(category) LIKE ?', ['%' . strtolower($request->category) . '%']);
        }
        if ($request->filled('brand')) {
            $brand = strtolower($request->brand);
            $like = '%' . $brand . '%';

            $query->where(function($q) use ($like) {
                $q->whereRaw('LOWER(brand) LIKE ?', [$like])
                  ->orWhereRaw('LOWER(brand_pc_part) LIKE ?', [$like]);
            });
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
                $query->where(function($q) use ($cats) {
                    foreach ($cats as $cat) {
                        $q->orWhereRaw('LOWER(category) LIKE ?', ['%' . $cat . '%']);
                    }
                });
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
        $product = is_numeric($id)
            ? Product::findOrFail((int) $id)
            : Product::where('slug', $id)->firstOrFail();

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

    /**
     * POST /api/products — create a product (staff only via middleware)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'caption'         => 'nullable|string|max:255',
            'category'        => 'required|string|max:100',
            'brand'           => 'nullable|string|max:100',
            'brand_pc_part'   => 'nullable|string|max:100',
            'warranty'        => 'nullable|string|max:100',
            'price'           => 'required|numeric|min:0',
            'stock'           => 'nullable|integer|min:0',
            'stock_status'    => 'nullable|string|max:100',
            'stock_details'   => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'is_featured'     => 'nullable|boolean',
            'is_hot'          => 'nullable|boolean',
            'image'           => 'nullable|string|max:500',
            'images'          => 'nullable|array',
            'images.*'        => 'nullable|string|max:500',
            'specs'           => 'nullable|array',
            'specs.*'         => 'nullable|string|max:500',
            'specs_title'     => 'nullable|string|max:255',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_hot']      = $request->boolean('is_hot');

        $product = Product::create($validated);

        return response()->json(['success' => true, 'data' => $product], 201);
    }

    /**
     * PUT /api/products/{id} — update a product (staff only via middleware)
     */
    public function update(Request $request, $id)
    {
        $product = is_numeric($id)
            ? Product::findOrFail((int) $id)
            : Product::where('slug', $id)->firstOrFail();

        $validated = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'caption'         => 'nullable|string|max:255',
            'category'        => 'sometimes|string|max:100',
            'brand'           => 'nullable|string|max:100',
            'brand_pc_part'   => 'nullable|string|max:100',
            'warranty'        => 'nullable|string|max:100',
            'price'           => 'sometimes|numeric|min:0',
            'stock'           => 'nullable|integer|min:0',
            'stock_status'    => 'nullable|string|max:100',
            'stock_details'   => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'is_featured'     => 'nullable|boolean',
            'is_hot'          => 'nullable|boolean',
            'image'           => 'nullable|string|max:500',
            'images'          => 'nullable|array',
            'images.*'        => 'nullable|string|max:500',
            'specs'           => 'nullable|array',
            'specs.*'         => 'nullable|string|max:500',
            'specs_title'     => 'nullable|string|max:255',
        ]);

        if ($request->has('is_featured')) $validated['is_featured'] = $request->boolean('is_featured');
        if ($request->has('is_hot'))      $validated['is_hot']      = $request->boolean('is_hot');

        $product->update($validated);

        return response()->json(['success' => true, 'data' => $product]);
    }

    /**
     * DELETE /api/products/{id} — delete a product (staff only via middleware)
     */
    public function destroy($id)
    {
        $product = is_numeric($id)
            ? Product::findOrFail((int) $id)
            : Product::where('slug', $id)->firstOrFail();
        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted.']);
    }
}
