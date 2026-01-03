<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class InventoryManager extends Component
{
    use WithFileUploads;

    // Properti Public
    public $products; 
    public $name;
    public $price;
    public $category_id; 
    public $description; 
    public $image;
    public $productId;
    public $isEditing = false;
    public $is_available = true;

    protected $rules = [
        'name' => 'required',
        'price' => 'required|numeric',
        'category_id' => 'nullable',
    ];

    public function render()
    {
        $user = auth()->user();
        
        // Ambil produk milik tenant
        $this->products = Product::where('tenant_id', $user->tenant_id)->get();
        
        // Ambil kategori milik tenant
        $categories = Category::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->get();
            
        return view('livewire.inventory-manager', [
            'categories' => $categories // PERBAIKAN DI SINI: Nama key diganti jadi 'categories' agar sesuai view
        ]);
    }

    public function save()
    {
        $this->validate();

        $user = auth()->user();

        // 1. Siapkan data untuk disimpan
        $data = [
            'tenant_id' => $user->tenant_id, // WAJIB ADA
            'name' => $this->name,
            // Buat slug unik
            'slug' => Str::slug($this->name) . '-' . Str::random(5), 
            'base_price' => $this->price,    // Mapping ke base_price
            'category_id' => $this->category_id ?: null,
            'description' => $this->description,
            'is_available' => $this->is_available,
        ];

        // 2. Handle Upload Gambar
        if ($this->image) {
            $data['image_url'] = $this->image->store('products', 'public');
        }

        // 3. Simpan atau Update
        if ($this->productId) {
            $product = Product::where('id', $this->productId)
                ->where('tenant_id', $user->tenant_id)
                ->first();
                
            if($product) {
                // Jangan update slug/tenant_id saat edit
                unset($data['slug']);
                unset($data['tenant_id']);
                $product->update($data);
            }
        } else {
            Product::create($data);
        }

        // 4. Reset Form & Kirim Notifikasi
        $this->resetInput();
        session()->flash('message', $this->productId ? 'Roti berhasil diupdate!' : 'Roti berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $product = Product::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();
        
        $this->productId = $id;
        $this->name = $product->name;
        $this->price = $product->base_price; // Ambil base_price
        $this->category_id = $product->category_id;
        $this->description = $product->description;
        $this->is_available = (bool) $product->is_available;
        $this->isEditing = true;
    }

    public function delete($id)
    {
        Product::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->delete();
            
        session()->flash('message', 'Roti dihapus.');
    }

    private function resetInput()
    {
        $this->reset(['name', 'price', 'category_id', 'description', 'image', 'productId', 'isEditing', 'is_available']);
    }
}