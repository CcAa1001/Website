<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Services\ImageService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductManager extends Component
{
    use WithFileUploads, WithPagination;

    // ==================== VIEW STATE ====================
    public $viewMode = 'grid'; // grid or table
    public $showModal = false;
    public $modalMode = 'create'; // create or edit
    
    // ==================== FORM PROPERTIES ====================
    public $productId;
    public $name;
    public $sku;
    public $slug;
    public $description;
    public $category_id;
    public $base_price;
    public $cost_price;
    public $preparation_time = 15;
    public $calories;
    public $tags = '';
    public $allergens = '';
    public $is_available = true;
    public $is_featured = false;
    public $is_taxable = true;
    public $tax_inclusive = true;
    public $sort_order = 0;
    
    // ==================== IMAGE PROPERTIES ====================
    public $imageFile;
    public $currentImage; // For preview
    public $imagePreview; // Temporary preview URL
    public $showImageUploader = false;
    
    // ==================== FILTERS ====================
    public $search = '';
    public $filterCategory = '';
    public $filterStatus = 'all';
    public $filterFeatured = 'all';
    public $sortBy = 'default';
    public $perPage = 15;
    
    // ==================== BULK ACTIONS ====================
    public $selectedProducts = [];
    public $selectAll = false;
    
    protected $paginationTheme = 'bootstrap';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'filterCategory' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
    ];

    protected $listeners = [
        'productSaved' => '$refresh',
        'productDeleted' => '$refresh',
    ];

    protected function rules()
    {
        $user = auth()->user();
        
        return [
            'name' => ['required', 'min:2', 'max:255'],
            'sku' => [
                'nullable',
                'max:50',
                Rule::unique('products', 'sku')
                    ->where('tenant_id', $user->tenant_id)
                    ->ignore($this->productId)
            ],
            'slug' => [
                'nullable',
                'max:255',
                Rule::unique('products', 'slug')
                    ->where('tenant_id', $user->tenant_id)
                    ->ignore($this->productId)
            ],
            'description' => 'nullable|max:2000',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0|max:99999999',
            'cost_price' => 'nullable|numeric|min:0|max:99999999',
            'imageFile' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'preparation_time' => 'nullable|integer|min:1|max:999',
            'calories' => 'nullable|integer|min:0|max:9999',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'tags' => 'nullable|string|max:1000',
            'allergens' => 'nullable|string|max:1000',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama produk wajib diisi',
        'category_id.required' => 'Kategori wajib dipilih',
        'base_price.required' => 'Harga wajib diisi',
        'imageFile.image' => 'File harus berupa gambar',
        'imageFile.max' => 'Ukuran gambar maksimal 2MB',
        'sku.unique' => 'SKU sudah digunakan',
        'slug.unique' => 'Slug sudah digunakan',
    ];

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
            ->with(['category', 'variants', 'primaryImage']);

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

        $products = $query->paginate($this->perPage);

        // Get categories
        $categories = Category::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Stats
        $stats = [
            'total' => Product::where('tenant_id', $user->tenant_id)->count(),
            'available' => Product::where('tenant_id', $user->tenant_id)->where('is_available', true)->count(),
            'unavailable' => Product::where('tenant_id', $user->tenant_id)->where('is_available', false)->count(),
            'featured' => Product::where('tenant_id', $user->tenant_id)->where('is_featured', true)->count(),
        ];

        return view('livewire.product-manager', [
            'products' => $products,
            'categories' => $categories,
            'stats' => $stats,
        ]);
    }

    // ==================== MODAL ACTIONS ====================

    public function openCreateModal()
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showModal = true;
        $this->dispatch('modal-opened');
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        $this->loadProduct($id);
        $this->modalMode = 'edit';
        $this->showModal = true;
        $this->dispatch('modal-opened');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->dispatch('modal-closed');
    }

    // ==================== CRUD OPERATIONS ====================

    public function save()
    {
        $this->validate();

        $user = auth()->user();

        DB::beginTransaction();
        try {
            // Prepare data
            $data = [
                'tenant_id' => $user->tenant_id,
                'name' => $this->name,
                'slug' => $this->slug ?: Str::slug($this->name) . '-' . Str::random(5),
                'sku' => $this->sku,
                'description' => $this->description,
                'category_id' => $this->category_id,
                'base_price' => $this->base_price,
                'cost_price' => $this->cost_price,
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

            // Create or update product
            if ($this->productId) {
                $product = Product::where('id', $this->productId)
                    ->where('tenant_id', $user->tenant_id)
                    ->firstOrFail();
                
                unset($data['tenant_id']);
                $product->update($data);
                
                $message = '✅ Produk berhasil diupdate!';
            } else {
                $product = Product::create($data);
                $message = '✅ Produk berhasil ditambahkan!';
            }

            // Handle image upload
            if ($this->imageFile) {
                // Delete old images
                foreach ($product->images as $oldImage) {
                    ImageService::deleteProductImage($oldImage);
                }

                // Upload new image
                ImageService::uploadProductImage(
                    $product->id,
                    $this->imageFile,
                    [
                        'alt_text' => $this->name,
                        'is_primary' => true,
                    ]
                );

                Log::info('Product image uploaded', ['product_id' => $product->id]);
            }

            DB::commit();
            
            $this->dispatch('show-toast', [
                'message' => $message,
                'type' => 'success'
            ]);
            
            $this->closeModal();
            $this->resetForm();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Product save failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->dispatch('show-toast', [
                'message' => '❌ Terjadi kesalahan: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function loadProduct($id)
    {
        $product = Product::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with(['primaryImage', 'images'])
            ->firstOrFail();

        $this->productId = $product->id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->slug = $product->slug;
        $this->description = $product->description;
        $this->category_id = $product->category_id;
        $this->base_price = $product->base_price;
        $this->cost_price = $product->cost_price;
        $this->preparation_time = $product->preparation_time ?? 15;
        $this->calories = $product->calories;
        $this->is_available = (bool)$product->is_available;
        $this->is_featured = (bool)$product->is_featured;
        $this->is_taxable = (bool)$product->is_taxable;
        $this->tax_inclusive = (bool)$product->tax_inclusive;
        $this->sort_order = $product->sort_order ?? 0;
        $this->tags = $this->arrayToString($product->tags);
        $this->allergens = $this->arrayToString($product->allergens);
        
        // Load current image
        if ($product->primaryImage) {
            $this->currentImage = $product->primaryImage->medium_url;
        } elseif ($product->image_url) {
            $this->currentImage = asset('storage/' . $product->image_url);
        }
    }

    public function duplicate($id)
    {
        $product = Product::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with(['images'])
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Duplicate product
            $newProduct = $product->replicate();
            $newProduct->name = $product->name . ' (Copy)';
            $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(5);
            $newProduct->sku = $product->sku ? $product->sku . '-COPY' : null;
            $newProduct->save();

            // Duplicate image
            if ($product->primaryImage && Storage::disk('public')->exists($product->primaryImage->original_path)) {
                $originalPath = Storage::disk('public')->path($product->primaryImage->original_path);
                
                $tempFile = new \Illuminate\Http\UploadedFile(
                    $originalPath,
                    $product->primaryImage->original_filename,
                    $product->primaryImage->mime_type,
                    null,
                    true
                );
                
                ImageService::uploadProductImage(
                    $newProduct->id,
                    $tempFile,
                    [
                        'alt_text' => $newProduct->name,
                        'is_primary' => true,
                    ]
                );
            }

            DB::commit();
            
            $this->dispatch('show-toast', [
                'message' => '✅ Produk berhasil diduplikasi!',
                'type' => 'success'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('show-toast', [
                'message' => '❌ Gagal menduplikasi: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $product = Product::where('id', $id)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->with('images')
                ->firstOrFail();

            // Delete images
            foreach ($product->images as $image) {
                ImageService::deleteProductImage($image);
            }

            // Delete product
            $product->delete();
            
            $this->dispatch('show-toast', [
                'message' => '✅ Produk berhasil dihapus!',
                'type' => 'success'
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'message' => '❌ Gagal menghapus: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function removeImage()
    {
        if ($this->productId) {
            try {
                $product = Product::where('id', $this->productId)
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->with('images')
                    ->firstOrFail();

                foreach ($product->images as $image) {
                    ImageService::deleteProductImage($image);
                }

                $this->currentImage = null;
                $this->imagePreview = null;
                
                $this->dispatch('show-toast', [
                    'message' => '✅ Gambar berhasil dihapus!',
                    'type' => 'success'
                ]);
                
            } catch (\Exception $e) {
                $this->dispatch('show-toast', [
                    'message' => '❌ Gagal menghapus gambar',
                    'type' => 'error'
                ]);
            }
        } else {
            $this->imageFile = null;
            $this->imagePreview = null;
        }
    }

    // ==================== QUICK ACTIONS ====================

    public function toggleAvailability($id)
    {
        $product = Product::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $product->update(['is_available' => !$product->is_available]);
        
        $status = $product->is_available ? 'aktif' : 'nonaktif';
        $this->dispatch('show-toast', [
            'message' => "✅ Produk {$status}!",
            'type' => 'success'
        ]);
    }

    public function toggleFeatured($id)
    {
        $product = Product::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $product->update(['is_featured' => !$product->is_featured]);
        
        $this->dispatch('show-toast', [
            'message' => '✅ Status featured berhasil diubah!',
            'type' => 'success'
        ]);
    }

    // ==================== BULK ACTIONS ====================

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedProducts = Product::where('tenant_id', auth()->user()->tenant_id)
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedProducts = [];
        }
    }

    public function bulkDelete()
    {
        if (empty($this->selectedProducts)) {
            return;
        }

        try {
            DB::beginTransaction();
            
            $products = Product::whereIn('id', $this->selectedProducts)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->with('images')
                ->get();

            foreach ($products as $product) {
                foreach ($product->images as $image) {
                    ImageService::deleteProductImage($image);
                }
                $product->delete();
            }

            DB::commit();
            
            $count = count($this->selectedProducts);
            $this->selectedProducts = [];
            $this->selectAll = false;
            
            $this->dispatch('show-toast', [
                'message' => "✅ {$count} produk berhasil dihapus!",
                'type' => 'success'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('show-toast', [
                'message' => '❌ Gagal menghapus produk',
                'type' => 'error'
            ]);
        }
    }

    public function bulkActivate()
    {
        if (empty($this->selectedProducts)) {
            return;
        }

        Product::whereIn('id', $this->selectedProducts)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->update(['is_available' => true]);

        $count = count($this->selectedProducts);
        $this->selectedProducts = [];
        $this->selectAll = false;
        
        $this->dispatch('show-toast', [
            'message' => "✅ {$count} produk berhasil diaktifkan!",
            'type' => 'success'
        ]);
    }

    public function bulkDeactivate()
    {
        if (empty($this->selectedProducts)) {
            return;
        }

        Product::whereIn('id', $this->selectedProducts)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->update(['is_available' => false]);

        $count = count($this->selectedProducts);
        $this->selectedProducts = [];
        $this->selectAll = false;
        
        $this->dispatch('show-toast', [
            'message' => "✅ {$count} produk berhasil dinonaktifkan!",
            'type' => 'success'
        ]);
    }

    // ==================== VIEW CONTROLS ====================

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterCategory', 'filterStatus', 'filterFeatured', 'sortBy']);
        $this->resetPage();
    }

    public function updatedImageFile()
    {
        $this->validate(['imageFile' => 'image|max:2048']);
        $this->imagePreview = $this->imageFile->temporaryUrl();
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
            'base_price', 'cost_price', 'preparation_time', 'calories',
            'tags', 'allergens', 'imageFile', 'currentImage', 'imagePreview'
        ]);
        
        $this->is_available = true;
        $this->is_featured = false;
        $this->is_taxable = true;
        $this->tax_inclusive = true;
        $this->preparation_time = 15;
        $this->sort_order = 0;
        
        $this->resetValidation();
    }
}