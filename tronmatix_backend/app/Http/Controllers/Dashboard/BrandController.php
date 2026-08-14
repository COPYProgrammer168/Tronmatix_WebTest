<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\ImageStorageService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(private readonly ImageStorageService $storage) {}

    public function index()
    {
        $brands = Brand::orderBy('order')->get();

        return view('dashboard.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('dashboard.brands.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateBrand($request);
        $validated['slug']      = unique_slug($validated['name'], Brand::class);
        $validated['image']     = $this->resolveImage($request, null);
        $validated['order']     = (int) $request->input('order', 0);
        $validated['is_active'] = $request->boolean('is_active', true);

        Brand::create($validated);

        return redirect()->route('dashboard.brands.index')
            ->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand)
    {
        return view('dashboard.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $this->validateBrand($request);
        $validated['slug']      = unique_slug($validated['name'], Brand::class, $brand->id);
        $validated['image']     = $this->resolveImage($request, $brand->image);
        $validated['order']     = (int) $request->input('order', $brand->order);
        $validated['is_active'] = $request->boolean('is_active', $brand->is_active);

        $brand->update($validated);

        return redirect()->route('dashboard.brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function toggle(Brand $brand)
    {
        $brand->update(['is_active' => ! $brand->is_active]);

        return redirect()->route('dashboard.brands.index')
            ->with('success', $brand->is_active ? 'Brand activated.' : 'Brand hidden.');
    }

    public function destroy(Brand $brand)
    {
        $this->storage->delete($brand->image);
        $brand->delete();

        return redirect()->route('dashboard.brands.index')
            ->with('success', 'Brand deleted.');
    }

    private function validateBrand(Request $request): array
    {
        return $request->validate([
            'name'         => 'required|string|max:100',
            'image_file'   => 'nullable|file|max:51200|mimes:jpg,jpeg,png,webp,gif',
            'image_url'    => 'nullable|string|max:500',
            'remove_image' => 'nullable',
            'order'        => 'nullable|integer|min:0',
            'is_active'    => 'nullable',
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
