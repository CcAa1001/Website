<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\ModifierGroup;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductManager extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    // ==================== PROPERTIES ====================
    public $productId;
    public $name;
    public $sku;
    public $slug;
    public $description;
    public $category_id;
    public $base_price;
    public $cost_price;
    public $image_url; 
    public $imageFile; 
    public $currentImageUrl; 
    public $product_type = 'single'; 
    public $preparation_time = 15;
    public $calories;
    public $is_available = true;
    public $is_featured = false;
    public $is_taxable = true;
    public $tax_inclusive = true;
    public $sort_order = 0;
    
    // Tags & Allergens
    public $tags = '';
    public $allergens = '';
    
    // UI State
    public $isEditing = false;
    public $activeTab = 'products';
    public $viewMode = 'grid';
    
    // MODAL CONTROL (Perbaikan Error $showModal)
    public $showModal = false; 
    public $modalMode = 'create'; // 'create' or 'edit'

    public $selectedProducts = []; 

    // ==================== FILTERS ====================
    public $search = '';
    public $filterCategory = '';
    public $filterStatus = 'all';
    public $filterFeatured = 'all';
    public $sortBy = 'default';

    protected function rules()
    {
        return [
            'name' => 'required|min:2|max:255',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'product_type' => 'required|in:single,variable',
            'imageFile' => 'nullable|image|max:2048', 
            
            'sku' => [
                'nullable', 'max:50',
                Rule::unique('products', 'sku')
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->ignore($this->productId)
            ],
            'slug' => [
                'nullable', 'max:255',
                Rule::unique('products', 'slug')
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->ignore($this->productId)
            ],
            
            'preparation_time' => 'nullable|integer|min:0',
            'calories' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'tags' => 'nullable|string|max:1000',
            'allergens' => 'nullable|string|max:1000',
        ];
    }

    public function mount()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    // ==================== RENDER ====================
    public function render()
    {
        $user = auth()->user();

        // 1. Query Products
        $query = Product::where('tenant_id', $user->tenant_id)
            ->with(['category']);

        // Search
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        // Filters
        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }

        if ($this->filterStatus !== 'all') {
            $query->where('is_available', $this->filterStatus === 'available');
        }

        if ($this->filterFeatured !== 'all') {
            $query->where('is_featured', $this->filterFeatured === 'featured');
        }

        // Sorting
        switch ($this->sortBy) {
            case 'name_asc': $query->orderBy('name', 'asc'); break;
            case 'name_desc': $query->orderBy('name', 'desc'); break;
            case 'price_low': $query->orderBy('base_price', 'asc'); break;
            case 'price_high': $query->orderBy('base_price', 'desc'); break;
            case 'newest': $query->orderBy('created_at', 'desc'); break;
            default: $query->orderBy('sort_order')->orderBy('name'); break;
        }

        $products = $query->paginate(15);

        // 2. Get Categories for Dropdown
        $categories = Category::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // 3. Stats
        $stats = [
            'total' => Product::where('tenant_id', $user->tenant_id)->count(),
            'available' => Product::where('tenant_id', $user->tenant_id)->where('is_available', true)->count(),
            'unavailable' => Product::where('tenant_id', $user->tenant_id)->where('is_available', false)->count(),
            'featured' => Product::where('tenant_id', $user->tenant_id)->where('is_featured', true)->count(),
        ];

        return view('livewire.product-manager', [
            'products' => $products,
            'categories' => $categories,
            'stats' => $stats
        ]);
    }

    // ==================== ACTIONS ====================

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->modalMode = 'create'; // Set mode create
        $this->showModal = true; // Buka modal
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
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
                'slug' => $this->slug ?: Str::slug($this->name) . '-' . Str::random(5),
                'sku' => $this->sku,
                'description' => $this->description,
                'category_id' => $this->category_id,
                'base_price' => $this->base_price,
                'cost_price' => $this->cost_price,
                'product_type' => $this->product_type,
                'preparation_time' => $this->preparation_time ?? 15,
                'calories' => $this->calories,
                'is_available' => $this->is_available,
                'is_featured' => $this->is_featured,
                'is_taxable' => $this->is_taxable,
                'tax_inclusive' => $this->tax_inclusive,
                'sort_order' => $this->sort_order ?? 0,
                'tags' => $this->parseTags($this->tags),
                'allergens' => $this->parseTags($this->allergens),
            ];

            // Handle Image Upload
            if ($this->imageFile) {
                if ($this->isEditing && $this->currentImageUrl) {
                    Storage::disk('public')->delete($this->currentImageUrl);
                }
                $data['image_url'] = $this->imageFile->store('products', 'public');
            } elseif ($this->image_url) {
                $data['image_url'] = $this->image_url;
            }

            if ($this->productId) {
                $product = Product::where('id', $this->productId)
                    ->where('tenant_id', $user->tenant_id)
                    ->firstOrFail();
                
                unset($data['tenant_id']); 
                $product->update($data);
                $message = 'Produk berhasil diupdate!';
            } else {
                Product::create($data);
                $message = 'Produk berhasil ditambahkan!';
            }

            DB::commit();
            session()->flash('message', $message);
            $this->closeModal(); // Tutup modal setelah save
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $product = Product::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $this->productId = $product->id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->slug = $product->slug;
        $this->description = $product->description;
        $this->category_id = $product->category_id;
        $this->base_price = $product->base_price;
        $this->cost_price = $product->cost_price;
        $this->currentImageUrl = $product->image_url; 
        $this->product_type = $product->product_type ?? 'single';
        $this->preparation_time = $product->preparation_time;
        $this->calories = $product->calories;
        $this->is_available = (bool)$product->is_available;
        $this->is_featured = (bool)$product->is_featured;
        $this->is_taxable = (bool)$product->is_taxable;
        $this->tax_inclusive = (bool)$product->tax_inclusive;
        $this->sort_order = $product->sort_order;
        $this->tags = $this->arrayToString($product->tags);
        $this->allergens = $this->arrayToString($product->allergens);
        
        $this->isEditing = true;
        $this->modalMode = 'edit';
        $this->imageFile = null; 
        
        $this->showModal = true;
    }

    public function delete($id)
    {
        $product = Product::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        if (ModifierGroup::where('product_id', $id)->exists()) {
            session()->flash('error', 'Produk memiliki modifier. Hapus modifier terlebih dahulu!');
            return;
        }

        if ($product->image_url && Storage::disk('public')->exists($product->image_url)) {
            Storage::disk('public')->delete($product->image_url);
        }

        $product->delete();
        session()->flash('message', 'Produk berhasil dihapus!');
    }

    public function duplicate($id)
    {
        $product = Product::where('id', $id)->where('tenant_id', auth()->user()->tenant_id)->firstOrFail();
        
        DB::transaction(function () use ($product) {
            $newProduct = $product->replicate();
            $newProduct->name = $product->name . ' (Copy)';
            $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(5);
            $newProduct->sku = $product->sku ? $product->sku . '-COPY-' . Str::random(3) : null;
            $newProduct->save();
        });

        session()->flash('message', 'Produk berhasil diduplikasi!');
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_available' => !$product->is_available]);
    }

    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_featured' => !$product->is_featured]);
    }

    public function removeImage()
    {
        $this->imageFile = null;
        $this->currentImageUrl = null;
        $this->image_url = null;
        
        if ($this->productId) {
            Product::where('id', $this->productId)->update(['image_url' => null]);
        }
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterCategory', 'filterStatus', 'filterFeatured', 'sortBy']);
        $this->resetPage();
    }

    // ==================== HELPERS ====================

    private function parseTags($string)
    {
        return empty($string) ? [] : array_map('trim', explode(',', $string));
    }

    private function arrayToString($array)
    {
        return (is_array($array) && count($array) > 0) ? implode(', ', $array) : '';
    }

    private function resetForm()
    {
        $this->reset([
            'productId', 'name', 'sku', 'slug', 'description', 'category_id',
            'base_price', 'cost_price', 'image_url', 'imageFile', 'currentImageUrl',
            'product_type', 'preparation_time', 'calories', 'sort_order',
            'tags', 'allergens', 'isEditing'
        ]);
        
        $this->is_available = true;
        $this->is_featured = false;
        $this->is_taxable = true;
        $this->preparation_time = 15;
    }

    public function bulkActivate() { /* Placeholder */ }
    public function bulkDeactivate() { /* Placeholder */ }
}