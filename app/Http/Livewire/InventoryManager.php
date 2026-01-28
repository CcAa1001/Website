<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\InventoryItem; // Menggunakan Model Inventory
use Illuminate\Validation\Rule;

class InventoryManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // ==================== PROPERTIES ====================
    public $itemId;
    public $name;
    public $sku;
    public $unit = 'kg'; // Default unit
    public $cost_per_unit;
    public $current_stock;
    public $reorder_level;
    public $is_active = true;

    // UI State
    public $isModalOpen = false;
    public $isEditMode = false;
    public $search = '';

    // ==================== RULES ====================
    protected function rules()
    {
        return [
            'name' => 'required|min:2|max:255',
            // Validasi SKU unik per tenant
            'sku' => [
                'nullable', 
                'max:50',
                Rule::unique('inventory_items', 'sku')
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->ignore($this->itemId)
            ],
            'unit' => 'required|string|max:20',
            'cost_per_unit' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ];
    }

    public function mount()
    {
        // Pastikan user login
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    // ==================== RENDER ====================
    public function render()
    {
        $user = auth()->user();

        // Ambil data inventory milik tenant user ini
        $query = InventoryItem::where('tenant_id', $user->tenant_id);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        $items = $query->orderBy('name')->paginate(10);

        return view('livewire.inventory-manager', [
            'items' => $items
        ]);
    }

    // ==================== ACTIONS ====================

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
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
        $this->cost_per_unit = $item->cost_per_unit;
        $this->current_stock = $item->current_stock;
        $this->reorder_level = $item->reorder_level;
        $this->is_active = (bool) $item->is_active;

        $this->isEditMode = true;
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
            'cost_per_unit' => $this->cost_per_unit,
            'current_stock' => $this->current_stock,
            'reorder_level' => $this->reorder_level ?? 0,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditMode) {
            $item = InventoryItem::where('id', $this->itemId)
                ->where('tenant_id', $user->tenant_id)
                ->firstOrFail();
                
            $item->update($data);
            session()->flash('message', 'Bahan baku berhasil diupdate.');
        } else {
            $data['tenant_id'] = $user->tenant_id;
            // Jika ingin inventory per outlet, uncomment baris ini:
            // $data['outlet_id'] = $user->outlet_id; 
            
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
            $item->delete();
            session()->flash('message', 'Item berhasil dihapus.');
        }
    }

    // ==================== HELPERS ====================

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->itemId = null;
        $this->name = '';
        $this->sku = '';
        $this->unit = 'kg'; // Reset ke default
        $this->cost_per_unit = '';
        $this->current_stock = '';
        $this->reorder_level = '';
        $this->is_active = true;
    }
}