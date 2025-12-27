<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Category;

class CategoryManager extends Component
{
    public $name;

    public function save()
    {
        $this->validate(['name' => 'required|unique:categories,name']);
        Category::create(['name' => $this->name]);
        $this->name = '';
        session()->flash('message', 'Kategori berhasil ditambah!');
    }

    public function delete($id)
    {
        Category::find($id)->delete();
    }

    public function render()
    {
        return view('livewire.category-manager', [
            'categories' => Category::all()
        ]);
    }
}