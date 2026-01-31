<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductManager extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Properties
    public $productId;
    public $name, $sku, $description;
    public $category_id;
    public $base_price, $cost_price;
    
    // Image Handling
    public $imageFile; // Uploaded file temp
    public $currentImageUrl; // Existing image path from DB
    
    // Settings
    public $product_type = 'single';
    public $is_available = true;
    public $is_featured = false;
    public $stock_status = 'in_stock';
    
    // UI States
    public $showModal = false;
    public $modalMode = 'create';
    public $search = '';
    public $filterCategory = '';

    // Listeners
    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        // Pastikan user login & punya tenant
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|min:2|max:255',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'sku' => [
                'nullable', 
                'max:50',
                Rule::unique('products', 'sku')
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->ignore($this->productId)
            ],
            'imageFile' => 'nullable|image|max:5120', // Max 5MB
        ];
    }

    // --- RENDER ---
    public function render()
    {
        $user = auth()->user();

        $query = Product::where('tenant_id', $user->tenant_id)
            ->with('category');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(10);
        
        $categories = Category::where('tenant_id', $user->tenant_id)->orderBy('name')->get();

        return view('livewire.product-manager', [
            'products' => $products,
            'categories' => $categories
        ])->layout('layouts.app', ['activePage' => 'products', 'titlePage' => 'Produk']);
    }

    // --- ACTIONS ---

    public function openCreateModal()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        
        // Security Check
        if ($product->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $this->productId = $product->id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->description = $product->description;
        $this->category_id = $product->category_id;
        $this->base_price = $product->base_price;
        $this->cost_price = $product->cost_price;
        $this->currentImageUrl = $product->image_url;
        $this->is_available = $product->is_available;
        $this->is_featured = $product->is_featured;

        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();
        $user = auth()->user();

        DB::beginTransaction();
        try {
            $data = [
                'tenant_id' => $user->tenant_id,
                'name' => $this->name,
                'slug' => Str::slug($this->name) . '-' . Str::random(4),
                'sku' => $this->sku,
                'description' => $this->description,
                'category_id' => $this->category_id,
                'base_price' => $this->base_price,
                'cost_price' => $this->cost_price,
                'is_available' => $this->is_available,
                'is_featured' => $this->is_featured,
            ];

            // Handle Image
            if ($this->imageFile) {
                // Hapus gambar lama jika ada dan sedang edit
                if ($this->modalMode === 'edit' && $this->currentImageUrl) {
                    Storage::disk('public')->delete($this->currentImageUrl);
                }
                
                $path = $this->imageFile->store('products', 'public');
                $data['image_url'] = $path;
            }

            if ($this->modalMode === 'edit') {
                $product = Product::findOrFail($this->productId);
                
                // Keep old slug if name hasn't changed drastically or handle slug updates specifically
                // For simplicity, we update slug only on create usually, but here we updated it above. 
                // Let's prevent slug collision issues on update if desired, but Random(4) handles it.
                
                // Jangan update tenant_id saat edit
                unset($data['tenant_id']); 
                if(!$this->imageFile) unset($data['image_url']);

                $product->update($data);
                session()->flash('message', 'Produk berhasil diperbarui.');
            } else {
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

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'productId', 'name', 'sku', 'description', 'category_id', 
            'base_price', 'cost_price', 'imageFile', 'currentImageUrl', 
            'is_available', 'is_featured'
        ]);
        $this->is_available = true; // Default
    }
}