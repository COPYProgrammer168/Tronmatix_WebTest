<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\MainCategory;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index()
    {
        $subCategories = SubCategory::with('mainCategory.category')
            ->withCount('brands')
            ->orderBy('order')
            ->get();

        return view('dashboard.sub-categories.index', compact('subCategories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSubCategory($request);
        $validated['slug']      = unique_slug($validated['name'], SubCategory::class);
        $validated['order']     = (int) $request->input('order', 0);
        $validated['is_active'] = $request->boolean('is_active', true);

        $subCategory = SubCategory::create($validated);

        return response()->json(['success' => true, 'data' => $subCategory]);
    }

    public function update(Request $request, SubCategory $subCategory)
    {
        $validated = $this->validateSubCategory($request);
        $validated['slug']      = unique_slug($validated['name'], SubCategory::class, $subCategory->id);
        $validated['order']     = (int) $request->input('order', $subCategory->order);
        $validated['is_active'] = $request->boolean('is_active', $subCategory->is_active);

        $subCategory->update($validated);

        return response()->json(['success' => true, 'data' => $subCategory]);
    }

    public function toggle(SubCategory $subCategory)
    {
        $subCategory->update(['is_active' => ! $subCategory->is_active]);

        return response()->json(['success' => true, 'data' => $subCategory]);
    }

    public function destroy(SubCategory $subCategory)
    {
        $subCategory->delete();

        return response()->json(['success' => true]);
    }

    private function validateSubCategory(Request $request): array
    {
        return $request->validate([
            'main_category_id' => [
                'required',
                'exists:main_categories,id',
                function ($attribute, $value, $fail) {
                    if ($value && ! MainCategory::whereKey($value)->where('is_active', true)->exists()) {
                        $fail('The selected main category is not active.');
                    }
                },
            ],
            'name'      => 'required|string|max:100',
            'order'     => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ]);
    }
}
