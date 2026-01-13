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

class ProductManager extends Component
{
    use WithFileUploads, WithPagination;

    // ==================== FORM PROPERTIES ====================
    public $productId;
    public $name;
    public $sku;
    public $slug;
    public $description;
    public $category_id;
    public $base_price;
    public $cost_price;
    public $image_url; // Can be file upload or URL
    public $imageFile; // For file uploads
    public $currentImageUrl; // Current saved image
    public $product_type = 'single'; // single or variable
    public $preparation_time = 15;
    public $calories;
    public $is_available = true;
    public $is_featured = false;
    public $is_taxable = true;
    public $tax_inclusive = true;
    public $sort_order = 0;
    
    // Tags & Allergens (JSON arrays)
    public $tags = '';
    public $allergens = '';
    
    // Variants (for variable products)
    public $variants = [];
    public $showVariants = false;
    
    // UI State
    public $isEditing = false;
    public $activeTab = 'products'; // products, variants

    // ==================== FILTERS ====================
    public $search = '';
    public $filterCategory = '';
    public $filterStatus = 'all'; // all, available, unavailable
    public $filterFeatured = 'all'; // all, featured, regular
    public $sortBy = 'default'; // default, name_asc, name_desc, price_low, price_high, newest
    
    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        $rules = [
            'name' => 'required|min:2|max:255',
            'sku' => 'nullable|max:50',
            'slug' => 'nullable|max:255',
            'description' => 'nullable|max:2000',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0|max:99999999',
            'cost_price' => 'nullable|numeric|min:0|max:99999999',
            'imageFile' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url' => 'nullable|url|max:500',
            'product_type' => 'required|in:single,variable',
            'preparation_time' => 'nullable|integer|min:1|max:999',
            'calories' => 'nullable|integer|min:0|max:9999',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'tags' => 'nullable|string|max:1000',
            'allergens' => 'nullable|string|max:1000',
        ];

        return $rules;
    }

    public function mount()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        $user = auth()->user();

        // Build query
        $query = Product::where('tenant_id', $user->tenant_id)
            ->with(['category', 'variants']);

        // Apply search
        if ($this->search) {
            $query->search($this->search);
        }

        // Apply filters
        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }

        if ($this->filterStatus === 'available') {
            $query->where('is_available', true);
        } elseif ($this->filterStatus === 'unavailable') {
            $query->where('is_available', false);
        }

        if ($this->filterFeatured === 'featured') {
            $query->where('is_featured', true);
        } elseif ($this->filterFeatured === 'regular') {
            $query->where('is_featured', false);
        }

        // Apply sorting
        $query->sortBy($this->sortBy);

        $products = $query->paginate(15);

        // Get categories
        $categories = Category::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.product-manager', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function save()
    {
        $this->validate();

        $user = auth()->user();

        DB::beginTransaction();
        try {
            // Prepare base data
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
                'preparation_time' => $this->preparation_time,
                'calories' => $this->calories,
                'is_available' => $this->is_available,
                'is_featured' => $this->is_featured,
                'is_taxable' => $this->is_taxable,
                'tax_inclusive' => $this->tax_inclusive,
                'sort_order' => $this->sort_order,
                'tags' => $this->parseTags($this->tags),
                'allergens' => $this->parseTags($this->allergens),
            ];

            // Handle image
            if ($this->imageFile) {
                // Delete old image if editing
                if ($this->isEditing && $this->currentImageUrl && !filter_var($this->currentImageUrl, FILTER_VALIDATE_URL)) {
                    Storage::disk('public')->delete($this->currentImageUrl);
                }
                // Upload new image
                $data['image_url'] = $this->imageFile->store('products', 'public');
            } elseif ($this->image_url) {
                // Use provided URL
                $data['image_url'] = $this->image_url;
            }

            // Save or update
            if ($this->productId) {
                $product = Product::where('id', $this->productId)
                    ->where('tenant_id', $user->tenant_id)
                    ->firstOrFail();
                
                unset($data['tenant_id']); // Don't update tenant_id
                $product->update($data);
                
                $message = 'Produk berhasil diupdate!';
            } else {
                $product = Product::create($data);
                $message = 'Produk berhasil ditambahkan!';
            }

            DB::commit();
            session()->flash('message', $message);
            $this->resetForm();
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $product = Product::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with('variants')
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
        $this->preparation_time = $product->preparation_time ?? 15;
        $this->calories = $product->calories;
        $this->is_available = (bool)$product->is_available;
        $this->is_featured = (bool)$product->is_featured;
        $this->is_taxable = (bool)$product->is_taxable;
        $this->tax_inclusive = (bool)$product->tax_inclusive;
        $this->sort_order = $product->sort_order ?? 0;
        $this->tags = $this->arrayToString($product->tags);
        $this->allergens = $this->arrayToString($product->allergens);
        $this->isEditing = true;

        // Load variants if variable product
        if ($product->product_type === 'variable') {
            $this->showVariants = true;
        }

        $this->dispatch('productLoaded');
    }

    public function duplicate($id)
    {
        $product = Product::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with('variants')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $newProduct = $product->replicate();
            $newProduct->name = $product->name . ' (Copy)';
            $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(5);
            $newProduct->sku = $product->sku ? $product->sku . '-COPY' : null;
            $newProduct->save();

            // Duplicate variants
            foreach ($product->variants as $variant) {
                $newVariant = $variant->replicate();
                $newVariant->product_id = $newProduct->id;
                $newVariant->save();
            }

            DB::commit();
            session()->flash('message', 'Produk berhasil diduplikasi!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menduplikasi: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $product = Product::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        // Check dependencies
        $hasModifierGroups = ModifierGroup::where('product_id', $id)->exists();
        if ($hasModifierGroups) {
            session()->flash('error', 'Hapus modifier groups terlebih dahulu!');
            return;
        }

        // Delete image if local
        if ($product->image_url && !filter_var($product->image_url, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($product->image_url);
        }

        // Soft delete
        $product->delete();
        session()->flash('message', 'Produk berhasil dihapus!');
    }

    public function toggleStatus($id)
    {
        $product = Product::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $product->update(['is_available' => !$product->is_available]);
        session()->flash('message', 'Status berhasil diubah!');
    }

    public function toggleFeatured($id)
    {
        $product = Product::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $product->update(['is_featured' => !$product->is_featured]);
        session()->flash('message', 'Status featured berhasil diubah!');
    }

    public function removeImage()
    {
        if ($this->currentImageUrl && !filter_var($this->currentImageUrl, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($this->currentImageUrl);
            
            if ($this->productId) {
                Product::where('id', $this->productId)->update(['image_url' => null]);
            }
            
            $this->currentImageUrl = null;
            session()->flash('message', 'Gambar berhasil dihapus!');
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

    // ==================== HELPER METHODS ====================

    private function parseTags($string)
    {
        if (empty($string)) {
            return [];
        }
        
        return array_map('trim', explode(',', $string));
    }

    private function arrayToString($array)
    {
        if (empty($array) || !is_array($array)) {
            return '';
        }
        
        return implode(', ', $array);
    }

    private function resetForm()
    {
        $this->reset([
            'productId', 'name', 'sku', 'slug', 'description', 'category_id',
            'base_price', 'cost_price', 'image_url', 'imageFile', 'currentImageUrl',
            'product_type', 'preparation_time', 'calories', 'sort_order',
            'tags', 'allergens', 'isEditing', 'showVariants'
        ]);
        
        $this->is_available = true;
        $this->is_featured = false;
        $this->is_taxable = true;
        $this->tax_inclusive = true;
        $this->preparation_time = 15;
        $this->sort_order = 0;
    }

    public function getFormTitleProperty()
    {
        return $this->isEditing ? 'Edit Produk' : 'Tambah Produk Baru';
    }
}