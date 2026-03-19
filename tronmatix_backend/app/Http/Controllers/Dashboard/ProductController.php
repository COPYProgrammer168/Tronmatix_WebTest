<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Product;
use App\Traits\StorageHelper;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use StorageHelper;

    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $term = '%'.$request->search.'%';
            $query->where(fn($q) => $q
                ->where('name', 'LIKE', $term)
                ->orWhere('category', 'LIKE', $term)
                ->orWhere('brand', 'LIKE', $term));
        }
        if ($request->filled('category')) $query->where('category', $request->category);

        $threshold = AdminSetting::int('notif_low_stock_threshold', 5);
        if ($request->filled('stock')) {
            match ($request->stock) {
                'out'   => $query->where('stock', '<=', 0),
                'low'   => $query->where('stock', '>', 0)->where('stock', '<=', $threshold),
                'in'    => $query->where(fn($q) => $q->whereNull('stock')->orWhere('stock', '>', 0)),
                default => null,
            };
        }
        if ($request->filled('filter')) {
            match ($request->filter) {
                'hot'      => $query->where('is_hot', true),
                'featured' => $query->where('is_featured', true),
                default    => null,
            };
        }

        $products = $query->latest()->paginate(20)->withQueryString();
        return view('dashboard.products', compact('products'));
    }

    public function create()
    {
        return view('dashboard.products-form', ['product' => null]);
    }

    public function store(Request $request)
    {
        $validated           = $this->validateProduct($request);
        $validated['images'] = $this->buildImagesArray($request);
        Product::create($validated);
        return redirect()->route('dashboard.products')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        return view('dashboard.products-form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product);

        if ($request->boolean('remove_image')) {
            foreach ($product->all_images as $img) $this->deleteStorageFile($img);
            $validated['images'] = [];
            $validated['image']  = null;
        } else {
            $all = $this->buildImagesArray($request);
            $validated['images'] = empty($all) ? $product->all_images : $all;
        }

        $product->update($validated);
        return redirect()->route('dashboard.products')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->all_images as $img) $this->deleteStorageFile($img);
        $product->delete();
        return redirect()->route('dashboard.products')->with('success', 'Product deleted.');
    }

    private function buildImagesArray(Request $request): array
    {
        $result = [];

        // Keep existing (already S3 URLs or legacy paths)
        foreach ((array) $request->input('existing_images', []) as $path) {
            $path = trim((string) $path);
            if ($path !== '') $result[] = $path;
        }

        // Upload new files to S3/R2
        if ($request->hasFile('image_files')) {
            foreach ((array) $request->file('image_files') as $file) {
                if ($file && $file->isValid()) {
                    $result[] = $this->storeFile($file, 'products');
                }
            }
        }

        // External URLs
        foreach ((array) $request->input('image_urls', []) as $url) {
            $url = trim((string) $url);
            if ($url !== '') $result[] = $url;
        }

        return array_values(array_unique(array_filter($result)));
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'category'          => 'required|string|max:100',
            'brand'             => 'nullable|string|max:100',
            'price'             => 'required|numeric|min:0',
            'stock'             => 'nullable|integer|min:0',
            'rating'            => 'nullable|numeric|min:0|max:5',
            'description'       => 'nullable|string',
            'image_files.*'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'image_urls.*'      => 'nullable|string|max:500',
            'existing_images.*' => 'nullable|string|max:500',
            'remove_image'      => 'nullable|boolean',
            'is_featured'       => 'nullable|boolean',
            'is_hot'            => 'nullable|boolean',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_hot']      = $request->boolean('is_hot');

        unset($validated['image_files'], $validated['image_urls'],
              $validated['existing_images'], $validated['remove_image']);

        return $validated;
    }
}
