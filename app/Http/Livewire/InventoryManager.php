<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\InventoryItem; 
use App\Models\Category;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class InventoryManager extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    // ==================== PROPERTIES ====================
    public $itemId;
    public $name;
    public $sku;
    
    // Properti Form
    public $category_id;
    public $description;
    public $cost_price;
    public $base_price;
    public $image;
    public $currentImage;

    public $unit = 'kg'; 
    public $current_stock;
    public $reorder_level;
    public $is_active = true;

    // Properti Tambahan (Untuk kompatibilitas View)
    public $is_featured = false;
    public $is_taxable = true;
    public $calories;
    public $preparation_time;
    public $sort_order;

    // UI State
    public $isModalOpen = false;
    public $isEditMode = false;
    public $isEditing = false;
    
    public $formTitle = 'Tambah Bahan Baku';
    public $search = '';
    
    // Filters
    public $filterCategory = '';
    public $filterStatus = 'all';

    // ==================== RULES ====================
    protected function rules()
    {
        return [
            'name' => 'required|min:2|max:255',
            'sku' => [
                'nullable', 
                'max:50',
                Rule::unique('inventory_items', 'sku')
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->ignore($this->itemId)
            ],
            'unit' => 'required|string|max:20',
            'cost_price' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            
            'is_featured' => 'boolean',
            'is_taxable' => 'boolean',
            'calories' => 'nullable|numeric|min:0',
            'preparation_time' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|numeric|min:0',
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

        $query = InventoryItem::where('tenant_id', $user->tenant_id);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->filterStatus !== 'all') {
            $query->where('is_active', $this->filterStatus === 'available');
        }

        // PERBAIKAN: Gunakan variabel $products agar sesuai dengan view @forelse($products as $product)
        $products = $query->orderBy('name')->paginate(10);
        
        $categories = Category::where('tenant_id', $user->tenant_id)->get();

        return view('livewire.inventory-manager', [
            'products' => $products, // <-- Diubah dari 'items' menjadi 'products'
            'categories' => $categories
        ])->layout('layouts.app', ['titlePage' => 'inventory']); 
    }

    // ==================== ACTIONS ====================

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->isEditing = false;
        $this->formTitle = 'Tambah Bahan Baku';
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $item = InventoryItem::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $this->itemId = $item->id;
        $this->name = $item->name;
        $this->sku = $item->sku;
        $this->unit = $item->unit;
        $this->cost_price = $item->cost_per_unit; 
        $this->current_stock = $item->current_stock;
        $this->reorder_level = $item->reorder_level;
        $this->is_active = (bool) $item->is_active;
        
        $this->description = $item->description ?? '';
        $this->currentImage = $item->image_url ?? null;
        
        $this->calories = $item->calories ?? null;
        $this->preparation_time = $item->preparation_time ?? null;
        $this->sort_order = $item->sort_order ?? 0;
        $this->is_featured = (bool) ($item->is_featured ?? false);
        $this->is_taxable = (bool) ($item->is_taxable ?? true);

        $this->isEditMode = true;
        $this->isEditing = true;
        $this->formTitle = 'Edit Bahan Baku';
        $this->isModalOpen = true;
    }

    public function store()
    {
        $this->validate();
        $user = auth()->user();

        $data = [
            'name' => $this->name,
            'sku' => $this->sku,
            'unit' => $this->unit,
            'cost_per_unit' => $this->cost_price,
            'current_stock' => $this->current_stock,
            'reorder_level' => $this->reorder_level ?? 0,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'calories' => $this->calories,
            'preparation_time' => $this->preparation_time,
            'sort_order' => $this->sort_order ?? 0,
            'is_featured' => $this->is_featured,
            'is_taxable' => $this->is_taxable,
        ];

        if ($this->image) {
            if ($this->isEditMode && $this->currentImage) {
                Storage::disk('public')->delete($this->currentImage);
            }
            $data['image_url'] = $this->image->store('inventory', 'public');
        }

        if ($this->isEditMode) {
            $item = InventoryItem::where('id', $this->itemId)
                ->where('tenant_id', $user->tenant_id)
                ->firstOrFail();
            $item->update($data);
            session()->flash('message', 'Bahan baku berhasil diupdate.');
        } else {
            $data['tenant_id'] = $user->tenant_id;
            InventoryItem::create($data);
            session()->flash('message', 'Bahan baku berhasil ditambahkan.');
        }

        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id)
    {
        $item = InventoryItem::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if ($item) {
            if ($item->image_url) {
                Storage::disk('public')->delete($item->image_url);
            }
            $item->delete();
            session()->flash('message', 'Item berhasil dihapus.');
        }
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetInputFields();
    }
    
    public function cancelEdit()
    {
        $this->closeModal();
    }
    
    public function clearFilters()
    {
        $this->reset(['search', 'filterCategory', 'filterStatus']);
        $this->resetPage();
    }

    private function resetInputFields()
    {
        $this->itemId = null;
        $this->name = '';
        $this->sku = '';
        $this->category_id = '';
        $this->description = '';
        $this->unit = 'kg';
        $this->cost_price = ''; 
        $this->base_price = '';
        $this->current_stock = '';
        $this->reorder_level = '';
        $this->is_active = true;
        $this->image = null;
        $this->currentImage = null;
        
        $this->calories = '';
        $this->preparation_time = '';
        $this->sort_order = '';
        $this->is_featured = false;
        $this->is_taxable = true;
        
        $this->isEditMode = false;
        $this->isEditing = false;
    }
}