<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MainCategory;
use Illuminate\Http\Request;

class MainCategoryController extends Controller
{
    public function index()
    {
        $mainCategories = MainCategory::with('category')
            ->withCount('subCategories')
            ->orderBy('order')
            ->get();

        return view('dashboard.main-categories.index', compact('mainCategories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateMainCategory($request);
        $validated['slug']      = unique_slug($validated['name'], MainCategory::class);
        $validated['order']     = (int) $request->input('order', 0);
        $validated['is_active'] = $request->boolean('is_active', true);

        $mainCategory = MainCategory::create($validated);

        return response()->json(['success' => true, 'data' => $mainCategory]);
    }

    public function update(Request $request, MainCategory $mainCategory)
    {
        $validated = $this->validateMainCategory($request);
        $validated['slug']      = unique_slug($validated['name'], MainCategory::class, $mainCategory->id);
        $validated['order']     = (int) $request->input('order', $mainCategory->order);
        $validated['is_active'] = $request->boolean('is_active', $mainCategory->is_active);

        $mainCategory->update($validated);

        return response()->json(['success' => true, 'data' => $mainCategory]);
    }

    public function toggle(MainCategory $mainCategory)
    {
        $mainCategory->update(['is_active' => ! $mainCategory->is_active]);

        return response()->json(['success' => true, 'data' => $mainCategory]);
    }

    public function destroy(MainCategory $mainCategory)
    {
        $mainCategory->delete();

        return response()->json(['success' => true]);
    }

    private function validateMainCategory(Request $request): array
    {
        return $request->validate([
            'category_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    if ($value && ! Category::whereKey($value)->where('is_active', true)->exists()) {
                        $fail('The selected category is not active.');
                    }
                },
            ],
            'name'      => 'required|string|max:100',
            'order'     => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ]);
    }
}
