<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ProductManager extends Component
{
    use WithFileUploads, WithPagination;

    // List view properties
    public string $search = '';
    public string $categoryFilter = '';
    public string $statusFilter = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 12;

    // Form properties
    public bool $showForm = false;
    public bool $isEditing = false;
    public ?string $editingId = null;

    // Product fields
    public string $name = '';
    public string $slug = '';
    public string $sku = '';
    public string $description = '';
    public ?string $category_id = null;
    public float $base_price = 0;
    public ?float $cost_price = null;
    public int $preparation_time = 15;
    public ?int $calories = null;
    public string $product_type = 'single';
    public bool $is_available = true;
    public bool $is_featured = false;
    public bool $is_taxable = true;
    public bool $tax_inclusive = true;
    public int $sort_order = 0;
    public array $tags = [];
    public array $allergens = [];
    public $image;
    public ?string $current_image = null;

    // Variants
    public array $variants = [];
    public bool $showVariants = false;

    // Modifier Groups
    public array $selectedModifierGroups = [];

    // Delete confirmation
    public bool $showDeleteModal = false;
    public ?string $deleteId = null;
    public string $deleteName = '';

    // Listeners
    protected $listeners = ['refreshProducts' => '$refresh'];

    // Query string
    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    // Validation rules
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $this->editingId,
            'sku' => 'nullable|string|max:50|unique:products,sku,' . $this->editingId,
            'description' => 'nullable|string',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'preparation_time' => 'required|integer|min:1',
            'calories' => 'nullable|integer|min:0',
            'product_type' => 'required|in:single,variant',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'is_taxable' => 'boolean',
            'tax_inclusive' => 'boolean',
            'sort_order' => 'integer|min:0',
            'image' => 'nullable|image|max:2048',
        ];
    }

    // Generate slug from name
    public function updatedName($value)
    {
        if (!$this->isEditing) {
            $this->slug = Str::slug($value);
        }
    }

    // Open form for new product
    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->isEditing = false;
    }

    // Open form for editing
    public function edit(string $id)
    {
        $product = Product::with(['variants', 'modifierGroups'])->findOrFail($id);

        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->sku = $product->sku ?? '';
        $this->description = $product->description ?? '';
        $this->category_id = $product->category_id;
        $this->base_price = $product->base_price;
        $this->cost_price = $product->cost_price;
        $this->preparation_time = $product->preparation_time;
        $this->calories = $product->calories;
        $this->product_type = $product->product_type;
        $this->is_available = $product->is_available;
        $this->is_featured = $product->is_featured;
        $this->is_taxable = $product->is_taxable;
        $this->tax_inclusive = $product->tax_inclusive;
        $this->sort_order = $product->sort_order;
        $this->tags = $product->tags ?? [];
        $this->allergens = $product->allergens ?? [];
        $this->current_image = $product->image_url;

        // Load variants
        $this->variants = $product->variants->map(function ($v) {
            return [
                'id' => $v->id,
                'name' => $v->name,
                'sku' => $v->sku ?? '',
                'price_adjustment' => $v->price_adjustment,
                'cost_adjustment' => $v->cost_adjustment ?? 0,
                'is_default' => $v->is_default,
                'is_available' => $v->is_available,
                'sort_order' => $v->sort_order,
            ];
        })->toArray();

        $this->showVariants = count($this->variants) > 0;

        // Load modifier groups
        $this->selectedModifierGroups = $product->modifierGroups->pluck('id')->toArray();

        $this->showForm = true;
        $this->isEditing = true;
    }

    // Save product
    public function save()
    {
        $this->validate();

        $data = [
            'tenant_id' => auth()->user()->tenant_id ?? session('tenant_id') ?? $this->getDefaultTenantId(),
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku ?: null,
            'description' => $this->description ?: null,
            'category_id' => $this->category_id ?: null,
            'base_price' => $this->base_price,
            'cost_price' => $this->cost_price,
            'preparation_time' => $this->preparation_time,
            'calories' => $this->calories,
            'product_type' => $this->product_type,
            'is_available' => $this->is_available,
            'is_featured' => $this->is_featured,
            'is_taxable' => $this->is_taxable,
            'tax_inclusive' => $this->tax_inclusive,
            'sort_order' => $this->sort_order,
            'tags' => $this->tags,
            'allergens' => $this->allergens,
        ];

        // Handle image upload
        if ($this->image) {
            // Delete old image if exists
            if ($this->current_image && Storage::disk('public')->exists($this->current_image)) {
                Storage::disk('public')->delete($this->current_image);
            }
            $data['image_url'] = $this->image->store('products', 'public');
        }

        if ($this->isEditing) {
            $product = Product::findOrFail($this->editingId);
            $product->update($data);
            $message = 'Produk berhasil diperbarui!';
        } else {
            $product = Product::create($data);
            $message = 'Produk berhasil ditambahkan!';
        }

        // Save variants
        $this->saveVariants($product);

        // Sync modifier groups
        $product->modifierGroups()->sync($this->selectedModifierGroups);

        $this->dispatch('alert', type: 'success', message: $message);
        $this->closeForm();
    }

    // Save product variants
    protected function saveVariants(Product $product)
    {
        if ($this->product_type !== 'variant') {
            // Delete all variants if switching to single
            $product->variants()->delete();
            return;
        }

        $existingIds = [];

        foreach ($this->variants as $index => $variantData) {
            if (!empty($variantData['name'])) {
                $variantPayload = [
                    'name' => $variantData['name'],
                    'sku' => $variantData['sku'] ?? null,
                    'price_adjustment' => $variantData['price_adjustment'] ?? 0,
                    'cost_adjustment' => $variantData['cost_adjustment'] ?? 0,
                    'is_default' => $variantData['is_default'] ?? false,
                    'is_available' => $variantData['is_available'] ?? true,
                    'sort_order' => $index,
                ];

                if (!empty($variantData['id'])) {
                    // Update existing
                    $variant = ProductVariant::find($variantData['id']);
                    if ($variant) {
                        $variant->update($variantPayload);
                        $existingIds[] = $variant->id;
                    }
                } else {
                    // Create new
                    $variant = $product->variants()->create($variantPayload);
                    $existingIds[] = $variant->id;
                }
            }
        }

        // Delete removed variants
        $product->variants()->whereNotIn('id', $existingIds)->delete();
    }

    // Add variant row
    public function addVariant()
    {
        $this->variants[] = [
            'id' => null,
            'name' => '',
            'sku' => '',
            'price_adjustment' => 0,
            'cost_adjustment' => 0,
            'is_default' => count($this->variants) === 0,
            'is_available' => true,
            'sort_order' => count($this->variants),
        ];
    }

    // Remove variant row
    public function removeVariant(int $index)
    {
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
    }

    // Set default variant
    public function setDefaultVariant(int $index)
    {
        foreach ($this->variants as $i => $v) {
            $this->variants[$i]['is_default'] = ($i === $index);
        }
    }

    // Toggle product availability
    public function toggleAvailability(string $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_available' => !$product->is_available]);

        $status = $product->is_available ? 'tersedia' : 'tidak tersedia';
        $this->dispatch('alert', type: 'success', message: "Produk {$product->name} sekarang {$status}");
    }

    // Toggle featured
    public function toggleFeatured(string $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_featured' => !$product->is_featured]);

        $status = $product->is_featured ? 'ditampilkan sebagai unggulan' : 'dihapus dari unggulan';
        $this->dispatch('alert', type: 'success', message: "Produk {$product->name} {$status}");
    }

    // Confirm delete
    public function confirmDelete(string $id)
    {
        $product = Product::findOrFail($id);
        $this->deleteId = $id;
        $this->deleteName = $product->name;
        $this->showDeleteModal = true;
    }

    // Delete product
    public function delete()
    {
        $product = Product::findOrFail($this->deleteId);

        // Delete image
        if ($product->image_url && Storage::disk('public')->exists($product->image_url)) {
            Storage::disk('public')->delete($product->image_url);
        }

        $product->delete();

        $this->dispatch('alert', type: 'success', message: "Produk {$this->deleteName} berhasil dihapus!");
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteName = '';
    }

    // Duplicate product
    public function duplicate(string $id)
    {
        $product = Product::with(['variants', 'modifierGroups'])->findOrFail($id);

        $newProduct = $product->replicate();
        $newProduct->name = $product->name . ' (Copy)';
        $newProduct->slug = $product->slug . '-copy-' . time();
        $newProduct->sku = $product->sku ? $product->sku . '-copy' : null;
        $newProduct->save();

        // Duplicate variants
        foreach ($product->variants as $variant) {
            $newVariant = $variant->replicate();
            $newVariant->product_id = $newProduct->id;
            $newVariant->sku = $variant->sku ? $variant->sku . '-copy' : null;
            $newVariant->save();
        }

        // Copy modifier groups
        $newProduct->modifierGroups()->sync($product->modifierGroups->pluck('id'));

        $this->dispatch('alert', type: 'success', message: "Produk berhasil diduplikat!");
    }

    // Close form
    public function closeForm()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    // Reset form
    public function resetForm()
    {
        $this->reset([
            'editingId', 'name', 'slug', 'sku', 'description', 'category_id',
            'base_price', 'cost_price', 'preparation_time', 'calories',
            'product_type', 'is_available', 'is_featured', 'is_taxable',
            'tax_inclusive', 'sort_order', 'tags', 'allergens', 'image',
            'current_image', 'variants', 'showVariants', 'selectedModifierGroups'
        ]);

        $this->base_price = 0;
        $this->preparation_time = 15;
        $this->is_available = true;
        $this->is_taxable = true;
        $this->tax_inclusive = true;
        $this->product_type = 'single';
    }

    // Clear filters
    public function clearFilters()
    {
        $this->reset(['search', 'categoryFilter', 'statusFilter']);
    }

    // Get default tenant ID (fallback)
    protected function getDefaultTenantId(): string
    {
        return \App\Models\Tenant::first()?->id ?? '';
    }

    // Sort by column
    public function sortBy(string $column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $products = Product::query()
            ->with(['category', 'variants'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'ilike', "%{$this->search}%")
                          ->orWhere('sku', 'ilike', "%{$this->search}%")
                          ->orWhere('description', 'ilike', "%{$this->search}%");
                });
            })
            ->when($this->categoryFilter, function ($q) {
                $q->where('category_id', $this->categoryFilter);
            })
            ->when($this->statusFilter !== '', function ($q) {
                if ($this->statusFilter === 'available') {
                    $q->where('is_available', true);
                } elseif ($this->statusFilter === 'unavailable') {
                    $q->where('is_available', false);
                } elseif ($this->statusFilter === 'featured') {
                    $q->where('is_featured', true);
                }
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        $modifierGroups = ModifierGroup::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.product-manager', [
            'products' => $products,
            'categories' => $categories,
            'modifierGroups' => $modifierGroups,
        ]);
    }
}
