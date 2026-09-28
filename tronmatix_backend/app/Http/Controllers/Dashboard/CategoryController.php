<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageStorageService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly ImageStorageService $storage) {}

    public function index()
    {
        $tree = Category::orderBy('order')->with([
            'mainCategories' => fn ($q) => $q->orderBy('order')->with([
                'subCategories' => fn ($q) => $q->orderBy('order')->with([
                    'brands' => fn ($q) => $q->orderBy('order'),
                ]),
            ]),
        ])->get();

        return view('dashboard.categories.index', compact('tree'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);
        $validated['slug']      = unique_slug($validated['name'], Category::class);
        $validated['image']     = $this->resolveImage($request, null);
        $validated['order']     = (int) $request->input('order', 0);
        $validated['is_active'] = $request->boolean('is_active', true);

        $category = Category::create($validated);

        return response()->json(['success' => true, 'data' => $category]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $this->validateCategory($request);
        $validated['slug']      = unique_slug($validated['name'], Category::class, $category->id);
        $validated['image']     = $this->resolveImage($request, $category->image);
        $validated['order']     = (int) $request->input('order', $category->order);
        $validated['is_active'] = $request->boolean('is_active', $category->is_active);

        $category->update($validated);

        return response()->json(['success' => true, 'data' => $category]);
    }

    public function toggle(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        return response()->json(['success' => true, 'data' => $category]);
    }

    public function destroy(Category $category)
    {
        $this->storage->delete($category->image);
        $category->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'orders' => 'required|json',
        ]);

        $orders = json_decode($validated['orders'], true);

        $models = [
            'category'      => \App\Models\Category::class,
            'main-category' => \App\Models\MainCategory::class,
            'sub-category'  => \App\Models\SubCategory::class,
            'brand'         => \App\Models\Brand::class,
        ];

        foreach ($orders as $entry) {
            $type = $entry['type'] ?? null;
            $id   = $entry['id'] ?? null;
            $order = (int) ($entry['order'] ?? 0);

            if (! $type || ! isset($models[$type]) || ! $id) {
                continue;
            }

            $model = $models[$type];
            $model::whereKey($id)->update(['order' => $order]);
        }

        return response()->json(['success' => true]);
    }

    private function validateCategory(Request $request): array
    {
        return $request->validate([
            'name'      => 'required|string|max:100',
            'image_file'=> 'nullable|file|max:51200|mimes:jpg,jpeg,png,webp,gif',
            'image_url' => 'nullable|string|max:500',
            'order'     => 'nullable|integer|min:0',
            'is_active' => 'nullable',
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

            return $this->storage->store($request->file('image_file'), 'categories');
        }

        if ($request->filled('image_url')) {
            $this->storage->delete($current);

            return $request->input('image_url');
        }

        return $current;
    }
}
