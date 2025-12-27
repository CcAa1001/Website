<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\Category;

class InventoryManager extends Component
{
    use WithFileUploads;

    public $name, $price, $category_id, $description, $image, $is_available = true;
    public $productId;
    public $isEditing = false;

    protected $rules = [
        'name' => 'required|min:3',
        'price' => 'required|numeric',
        'category_id' => 'required',
        'image' => 'nullable|image|max:1024',
    ];

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'price' => $this->price,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'is_available' => $this->is_available,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('products', 'public');
        }

        Product::updateOrCreate(['id' => $this->productId], $data);

        $this->reset();
        session()->flash('message', 'Product saved successfully!');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $id;
        $this->name = $product->name;
        $this->price = $product->price;
        $this->category_id = $product->category_id;
        $this->description = $product->description;
        $this->is_available = $product->is_available;
        $this->isEditing = true;
    }

    public function render()
    {
        return view('livewire.inventory-manager', [
            'products' => Product::with('category')->get(),
            'categories' => Category::all()
        ]);
    }
}