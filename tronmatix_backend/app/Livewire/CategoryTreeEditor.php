<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;

class CategoryTreeEditor extends Component
{
    public $categories;

    public function mount()
    {
        $this->categories = Category::with('mainCategories.subCategories.brands')
            ->orderBy('order')
            ->get();
    }

    public function render()
    {
        return view('livewire.category-tree-editor');
    }
}
