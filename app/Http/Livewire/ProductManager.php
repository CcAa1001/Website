<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ProductManager extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Properties
    public $productId;
    public $name, $sku, $description;
    public $category_id;
    public $base_price = 0, $cost_price = 0;
    
    // Inventory
    public $stock = 0;
    public $min_stock = 5;
    
    // Settings
    public $is_available = true;
    public $is_featured = false;
    // public $stock_status = 'in_stock'; // Kita pakai logic stock angka saja agar lebih akurat

    // Image Handling
    public $imageFile; // Uploaded file temp
    public $currentImageUrl; // Existing image path from DB

    // UI States
    public $showModal = false;
    public $modalMode = 'create';
    public $search = '';
    public $filterCategory = '';

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        $user = auth()->user();

        $query = Product::where('tenant_id', $user->tenant_id)
            ->with('category');

        // Search
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        // Filter Category
        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(12); // Grid 3x4 = 12 items
        
        $categories = Category::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.product-manager', [
            'products' => $products,
            'categories' => $categories
        ])->layout('layouts.app', ['activePage' => 'products', 'titlePage' => 'Produk']);
    }

    // --- ACTIONS ---

    public function openCreateModal()
    {
        $this->resetForm();
        $this->sku = strtoupper(Str::random(8)); // Auto SKU
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        
        if ($product->tenant_id !== auth()->user()->tenant_id) abort(403);

        $this->productId = $product->id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->description = $product->description;
        $this->category_id = $product->category_id;
        $this->base_price = $product->base_price;
        $this->cost_price = $product->cost_price;
        $this->stock = $product->stock; // Asumsi ada kolom stock
        $this->min_stock = $product->min_stock; // Asumsi ada kolom min_stock
        $this->currentImageUrl = $product->image_url;
        $this->is_available = $product->is_available;
        $this->is_featured = $product->is_featured;

        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:2|max:255',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'sku' => [
                'nullable', 
                'max:50',
                Rule::unique('products', 'sku')
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->ignore($this->productId)
            ],
            'imageFile' => 'nullable|image|max:5120', // Max 5MB
        ]);

        $user = auth()->user();

        DB::beginTransaction();
        try {
            $data = [
                'tenant_id' => $user->tenant_id,
                'name' => $this->name,
                // Slug hanya dibuat saat create, atau update jika mau (disini kita keep simple)
                'sku' => $this->sku ?: strtoupper(Str::random(8)),
                'description' => $this->description,
                'category_id' => $this->category_id,
                'base_price' => $this->base_price,
                'cost_price' => $this->cost_price ?? 0,
                'stock' => $this->stock ?? 0,
                'min_stock' => $this->min_stock ?? 5,
                'is_available' => $this->is_available,
                'is_featured' => $this->is_featured,
            ];

            // Handle Image
            if ($this->imageFile) {
                if ($this->modalMode === 'edit' && $this->currentImageUrl) {
                    Storage::disk('public')->delete($this->currentImageUrl);
                }
                $data['image_url'] = $this->imageFile->store('products', 'public');
            }

            if ($this->modalMode === 'edit') {
                $product = Product::findOrFail($this->productId);
                
                // Jangan update slug saat edit untuk menjaga SEO/Link (optional)
                // unset($data['slug']); 
                
                $product->update($data);
                session()->flash('message', 'Produk berhasil diperbarui.');
            } else {
                $data['slug'] = Str::slug($this->name) . '-' . Str::random(4);
                Product::create($data);
                session()->flash('message', 'Produk baru berhasil ditambahkan.');
            }

            DB::commit();
            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        if ($product->tenant_id !== auth()->user()->tenant_id) return;

        if ($product->image_url) {
            Storage::disk('public')->delete($product->image_url);
        }
        
        $product->delete();
        session()->flash('message', 'Produk dihapus.');
    }

    public function removeImage()
    {
        if ($this->currentImageUrl) {
            Storage::disk('public')->delete($this->currentImageUrl);
            
            if ($this->productId) {
                Product::find($this->productId)->update(['image_url' => null]);
            }
            
            $this->currentImageUrl = null;
            session()->flash('message', 'Foto produk dihapus.');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'productId', 'name', 'sku', 'description', 'category_id', 
            'base_price', 'cost_price', 'stock', 'min_stock',
            'imageFile', 'currentImageUrl', 'is_available', 'is_featured'
        ]);
        $this->is_available = true;
    }
}