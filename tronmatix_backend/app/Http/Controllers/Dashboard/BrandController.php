<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\SubCategory;
use App\Services\ImageStorageService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(private readonly ImageStorageService $storage) {}

    public function index()
    {
        $brands = Brand::with('subCategory.mainCategory')
            ->orderBy('order')
            ->get();

        return view('dashboard.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateBrand($request);
        $validated['slug']      = unique_slug($validated['name'], Brand::class);
        $validated['image']     = $this->resolveImage($request, null);
        $validated['order']     = (int) $request->input('order', 0);
        $validated['is_active'] = $request->boolean('is_active', true);

        $brand = Brand::create($validated);

        return response()->json(['success' => true, 'data' => $brand]);
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $this->validateBrand($request);
        $validated['slug']      = unique_slug($validated['name'], Brand::class, $brand->id);
        $validated['image']     = $this->resolveImage($request, $brand->image);
        $validated['order']     = (int) $request->input('order', $brand->order);
        $validated['is_active'] = $request->boolean('is_active', $brand->is_active);

        $brand->update($validated);

        return response()->json(['success' => true, 'data' => $brand]);
    }

    public function toggle(Brand $brand)
    {
        $brand->update(['is_active' => ! $brand->is_active]);

        return response()->json(['success' => true, 'data' => $brand]);
    }

    public function destroy(Brand $brand)
    {
        $this->storage->delete($brand->image);
        $brand->delete();

        return response()->json(['success' => true]);
    }

    private function validateBrand(Request $request): array
    {
        return $request->validate([
            'sub_category_id' => [
                'required',
                'exists:sub_categories,id',
                function ($attribute, $value, $fail) {
                    if ($value && ! SubCategory::whereKey($value)->where('is_active', true)->exists()) {
                        $fail('The selected sub category is not active.');
                    }
                },
            ],
            'name'       => 'required|string|max:100',
            'image_file' => 'nullable|file|max:51200|mimes:jpg,jpeg,png,webp,gif',
            'image_url'  => 'nullable|string|max:500',
            'order'      => 'nullable|integer|min:0',
            'is_active'  => 'nullable',
        ]);
    }

    private function resolveImage(Request $request, ?string $current): ?string
    {
        $f = $request->files->get('image_file');
        if ($f && $f->getError() === UPLOAD_ERR_INI_SIZE) {
            return back()->withErrors(['image_file' => 'File too large (exceeds server PHP limit).'])->withInput();
        }

        if ($request->boolean('remove_image')) {
            $this->storage->delete($current);

            return null;
        }

        if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
            $this->storage->delete($current);

            return $this->storage->store($request->file('image_file'), 'brands');
        }

        if ($request->filled('image_url')) {
            $this->storage->delete($current);

            return $request->input('image_url');
        }

        return $current;
    }
}
