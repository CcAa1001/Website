<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Category;
use Illuminate\Support\Str; // Penting untuk Slug

class CategoryManager extends Component
{
    public $name;
    public $categories;

    public function render()
    {
        // Ambil kategori milik tenant ini saja
        $this->categories = Category::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('livewire.category-manager');
    }

    public function save()
    {
        $this->validate(['name' => 'required']);

        Category::create([
            'tenant_id' => auth()->user()->tenant_id, // WAJIB ADA
            'name' => $this->name,
            'slug' => Str::slug($this->name) . '-' . Str::random(5), // WAJIB ADA & UNIK
            'is_active' => true
        ]);

        $this->name = '';
        session()->flash('message', 'Kategori baru berhasil ditambahkan!');
    }

    public function delete($id)
    {
        $category = Category::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();
            
        if($category) {
            $category->delete();
            session()->flash('message', 'Kategori dihapus.');
        }
    }
}