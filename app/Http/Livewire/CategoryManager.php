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
        
        Category::create([
            'name' => $this->name,
            'user_id' => auth()->id() // Perbaikan: Ambil ID admin yang sedang login
        ]);

        $this->name = '';
        session()->flash('message', 'Kategori baru berhasil ditambahkan!');
    }

    public function delete($id)
    {
        Category::findOrFail($id)->delete();
        session()->flash('message', 'Kategori dihapus.');
    }

    public function render()
    {
        return view('livewire.category-manager', [
            'categories' => Category::orderBy('created_at', 'desc')->get()
        ])->layout('layouts.app');
    }
}