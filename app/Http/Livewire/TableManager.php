<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Table;
use App\Models\TableArea;
use App\Models\Outlet;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TableManager extends Component
{
    // Table Properties
    public $tableId;
    public $outlet_id;
    public $table_area_id;
    public $table_number;
    public $capacity = 4;
    public $qr_code;
    public $table_sort_order = 0;
    public $is_table_active = true;
    public $isEditingTable = false;

    // Area Properties
    public $areaId;
    public $areaName;
    public $area_sort_order = 0;
    public $is_area_active = true;
    public $isEditingArea = false;

    // UI State
    public $currentView = 'tables'; // 'tables' or 'areas'
    public $selectedOutlet;
    public $selectedArea;
    public $showQRModal = false;
    public $qrCodeUrl;

    protected $rules = [
        'outlet_id' => 'required|exists:outlets,id',
        'table_number' => 'required|max:20',
        'capacity' => 'required|integer|min:1|max:50',
        'table_sort_order' => 'integer|min:0',
    ];

    protected $areaRules = [
        'outlet_id' => 'required|exists:outlets,id',
        'areaName' => 'required|min:2|max:100',
        'area_sort_order' => 'integer|min:0',
    ];

    public function mount()
    {
        $user = auth()->user();
        
        // Set default outlet (user's outlet or first available)
        $this->selectedOutlet = $user->outlet_id ?? Outlet::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->first()?->id;
        
        $this->outlet_id = $this->selectedOutlet;
    }

    public function render()
    {
        $user = auth()->user();

        // Get outlets for dropdown
        $outlets = Outlet::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get tables and areas based on selected outlet
        $tables = collect();
        $areas = collect();

        if ($this->selectedOutlet) {
            $query = Table::where('outlet_id', $this->selectedOutlet)
                ->with('area');

            // Apply area filter if selected
            if ($this->selectedArea) {
                $query->where('table_area_id', $this->selectedArea);
            }

            $tables = $query->orderBy('table_area_id')
                ->orderBy('sort_order')
                ->orderBy('table_number')
                ->get();

            $areas = TableArea::where('outlet_id', $this->selectedOutlet)
                ->withCount('tables')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        return view('livewire.table-manager', [
            'outlets' => $outlets,
            'tables' => $tables,
            'areas' => $areas,
        ]);
    }

    // ==================== TABLE METHODS ====================

    public function saveTable()
    {
        $this->validate();

        $user = auth()->user();

        // Check if table number already exists in this outlet
        $exists = Table::where('outlet_id', $this->outlet_id)
            ->where('table_number', $this->table_number)
            ->when($this->tableId, function($q) {
                $q->where('id', '!=', $this->tableId);
            })
            ->exists();

        if ($exists) {
            session()->flash('error', 'Nomor meja sudah digunakan di outlet ini!');
            return;
        }

        $data = [
            'outlet_id' => $this->outlet_id,
            'table_area_id' => $this->table_area_id,
            'table_number' => $this->table_number,
            'capacity' => $this->capacity,
            'sort_order' => $this->table_sort_order,
            'is_active' => $this->is_table_active,
            'status' => 'available',
        ];

        // Generate QR code identifier if not editing or if outlet changed
        if (!$this->tableId || !$this->qr_code) {
            $data['qr_code'] = Str::random(32);
        }

        if ($this->tableId) {
            $table = Table::findOrFail($this->tableId);
            $table->update($data);
            session()->flash('message', 'Meja berhasil diupdate!');
        } else {
            Table::create($data);
            session()->flash('message', 'Meja berhasil ditambahkan!');
        }

        $this->resetTableForm();
    }

    public function editTable($id)
    {
        $table = Table::findOrFail($id);

        $this->tableId = $table->id;
        $this->outlet_id = $table->outlet_id;
        $this->table_area_id = $table->table_area_id;
        $this->table_number = $table->table_number;
        $this->capacity = $table->capacity;
        $this->qr_code = $table->qr_code;
        $this->table_sort_order = $table->sort_order;
        $this->is_table_active = $table->is_active;
        $this->isEditingTable = true;
    }

    public function deleteTable($id)
    {
        $table = Table::findOrFail($id);

        // Check if table has active orders
        if ($table->current_order_id) {
            session()->flash('error', 'Meja tidak dapat dihapus karena masih ada pesanan aktif!');
            return;
        }

        $table->delete();
        session()->flash('message', 'Meja berhasil dihapus!');
    }

    public function toggleTableStatus($id)
    {
        $table = Table::findOrFail($id);
        $table->update(['is_active' => !$table->is_active]);
        session()->flash('message', 'Status meja berhasil diubah!');
    }

    public function regenerateQR($id)
    {
        $table = Table::findOrFail($id);
        $table->update(['qr_code' => Str::random(32)]);
        session()->flash('message', 'QR Code berhasil di-regenerate!');
    }

    public function showQR($id)
    {
        $table = Table::findOrFail($id);
        $this->qrCodeUrl = route('public.menu', $table->table_number);
        $this->showQRModal = true;
    }

    public function bulkCreateTables()
    {
        if (!$this->outlet_id) {
            session()->flash('error', 'Pilih outlet terlebih dahulu!');
            return;
        }

        // Example: Create 10 tables automatically
        $startNumber = 1;
        $endNumber = 10;
        $created = 0;

        DB::beginTransaction();
        try {
            for ($i = $startNumber; $i <= $endNumber; $i++) {
                $exists = Table::where('outlet_id', $this->outlet_id)
                    ->where('table_number', (string)$i)
                    ->exists();

                if (!$exists) {
                    Table::create([
                        'outlet_id' => $this->outlet_id,
                        'table_area_id' => $this->table_area_id,
                        'table_number' => (string)$i,
                        'capacity' => 4,
                        'qr_code' => Str::random(32),
                        'sort_order' => $i,
                        'is_active' => true,
                        'status' => 'available',
                    ]);
                    $created++;
                }
            }

            DB::commit();
            session()->flash('message', "$created meja berhasil dibuat otomatis!");
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal membuat meja: ' . $e->getMessage());
        }
    }

    public function cancelTableEdit()
    {
        $this->resetTableForm();
    }

    // ==================== AREA METHODS ====================

    public function saveArea()
    {
        $this->validate($this->areaRules);

        $data = [
            'outlet_id' => $this->outlet_id,
            'name' => $this->areaName,
            'sort_order' => $this->area_sort_order,
            'is_active' => $this->is_area_active,
        ];

        if ($this->areaId) {
            $area = TableArea::findOrFail($this->areaId);
            $area->update($data);
            session()->flash('message', 'Area berhasil diupdate!');
        } else {
            TableArea::create($data);
            session()->flash('message', 'Area berhasil ditambahkan!');
        }

        $this->resetAreaForm();
    }

    public function editArea($id)
    {
        $area = TableArea::findOrFail($id);

        $this->areaId = $area->id;
        $this->areaName = $area->name;
        $this->area_sort_order = $area->sort_order;
        $this->is_area_active = $area->is_active;
        $this->isEditingArea = true;
    }

    public function deleteArea($id)
    {
        $area = TableArea::findOrFail($id);

        // Check if area has tables
        if ($area->tables()->count() > 0) {
            session()->flash('error', 'Area tidak dapat dihapus karena masih memiliki meja!');
            return;
        }

        $area->delete();
        session()->flash('message', 'Area berhasil dihapus!');
    }

    public function toggleAreaStatus($id)
    {
        $area = TableArea::findOrFail($id);
        $area->update(['is_active' => !$area->is_active]);
        session()->flash('message', 'Status area berhasil diubah!');
    }

    public function cancelAreaEdit()
    {
        $this->resetAreaForm();
    }

    // ==================== HELPER METHODS ====================

    public function updatedSelectedOutlet($value)
    {
        $this->outlet_id = $value;
        $this->selectedArea = null;
        $this->resetTableForm();
        $this->resetAreaForm();
    }

    public function updatedSelectedArea()
    {
        // Just trigger re-render when area filter changes
    }

    private function resetTableForm()
    {
        $this->reset([
            'tableId', 'table_area_id', 'table_number', 'capacity',
            'qr_code', 'table_sort_order', 'is_table_active', 'isEditingTable'
        ]);
        $this->capacity = 4;
        $this->table_sort_order = 0;
        $this->is_table_active = true;
    }

    private function resetAreaForm()
    {
        $this->reset([
            'areaId', 'areaName', 'area_sort_order', 'is_area_active', 'isEditingArea'
        ]);
        $this->area_sort_order = 0;
        $this->is_area_active = true;
    }

    public function getTableFormTitleProperty()
    {
        return $this->isEditingTable ? 'Edit Meja' : 'Tambah Meja Baru';
    }

    public function getAreaFormTitleProperty()
    {
        return $this->isEditingArea ? 'Edit Area' : 'Tambah Area Baru';
    }
}