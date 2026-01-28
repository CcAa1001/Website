<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Table;
use App\Models\TableArea;
use App\Models\Outlet;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
// [WAJIB] Import QR Library
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
    public $selectedOutlet;
    public $selectedArea;
    
    // QR Code Modal
    public $showQRModal = false;
    public $viewingTable = null; 
    public $qrCodeUrl;           
    public $qrCodeValue;         
    public $generatedQrSvg;      

    protected function rules()
    {
        return [
            'outlet_id' => 'required|exists:outlets,id',
            'table_number' => [
                'required', 'max:20',
                Rule::unique('tables', 'table_number')
                    ->where('outlet_id', $this->outlet_id)
                    ->ignore($this->tableId)
            ],
            'qr_code' => [
                'nullable', 'string', 'max:255',
                Rule::unique('tables', 'qr_code')->ignore($this->tableId)
            ],
            'capacity' => 'required|integer|min:1|max:50',
            'table_sort_order' => 'integer|min:0',
        ];
    }

    protected $areaRules = [
        'outlet_id' => 'required|exists:outlets,id',
        'areaName' => 'required|min:2|max:100',
        'area_sort_order' => 'integer|min:0',
    ];

    public function mount()
    {
        $user = auth()->user();
        $this->selectedOutlet = $user->outlet_id ?? Outlet::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->first()?->id;
        $this->outlet_id = $this->selectedOutlet;
    }

    public function render()
    {
        $user = auth()->user();
        $outlets = Outlet::where('tenant_id', $user->tenant_id)->orderBy('name')->get();

        $tables = collect();
        $areas = collect();

        if ($this->selectedOutlet) {
            $query = Table::where('outlet_id', $this->selectedOutlet)->with('tableArea');
            if ($this->selectedArea) {
                $query->where('table_area_id', $this->selectedArea);
            }
            $tables = $query->orderBy('table_area_id')->orderBy('sort_order')->orderBy('table_number')->get();

            $areas = TableArea::where('outlet_id', $this->selectedOutlet)
                ->withCount('tables')->orderBy('sort_order')->orderBy('name')->get();
        }

        return view('livewire.table-manager', [
            'outlets' => $outlets,
            'tables' => $tables,
            'areas' => $areas,
        ])
        // [FIX] Sidebar Active State
        ->layout('layouts.app', ['activePage' => 'tables']);
    }

    // ==================== QR CODE ACTIONS ====================

    public function showQR($id)
    {
        $table = Table::with('tableArea')->findOrFail($id);
        $this->viewingTable = $table;
        
        // [SMART LOGIC] Menggunakan accessor dari Model (Cek Google vs Internal)
        $fullUrl = $table->qr_url;
        
        $this->qrCodeUrl = $fullUrl;
        $this->qrCodeValue = $table->qr_code;

        // Generate QR Code SVG
        if (class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            $this->generatedQrSvg = QrCode::size(250)
                ->color(0, 0, 0)
                ->backgroundColor(255, 255, 255)
                ->margin(2)
                ->generate($fullUrl);
        } else {
            $this->generatedQrSvg = '<div class="text-danger p-3 border border-danger">Library QR Error. Run: composer require simplesoftwareio/simple-qrcode</div>';
        }

        $this->showQRModal = true;
    }

    public function regenerateQR($id)
    {
        $table = Table::findOrFail($id);
        $newCode = Str::random(16); // Kode Acak
        $table->update(['qr_code' => $newCode]);
        
        if ($this->viewingTable && $this->viewingTable->id == $id) {
            $this->showQR($id);
        }
        
        session()->flash('message', 'QR Code baru berhasil dibuat!');
    }

    // ==================== TABLE CRUD ====================

    public function saveTable()
    {
        $this->validate();

        // Auto Generate QR jika kosong
        if (empty($this->qr_code)) {
            $this->qr_code = Str::random(16);
        }

        $data = [
            'outlet_id' => $this->outlet_id,
            'table_area_id' => $this->table_area_id,
            'table_number' => $this->table_number,
            'capacity' => $this->capacity,
            'qr_code' => $this->qr_code,
            'sort_order' => $this->table_sort_order,
            'is_active' => $this->is_table_active,
        ];

        if (!$this->tableId) $data['status'] = 'available';

        if ($this->tableId) {
            Table::findOrFail($this->tableId)->update($data);
            session()->flash('message', 'Meja berhasil diupdate!');
        } else {
            Table::create($data);
            session()->flash('message', 'Meja berhasil ditambahkan!');
        }
        
        // Tutup Modal via JS Dispatch
        $this->dispatch('close-modal');
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
        $this->is_table_active = (bool)$table->is_active;
        $this->isEditingTable = true;
    }

    public function deleteTable($id)
    {
        $table = Table::findOrFail($id);
        if ($table->current_order_id) {
            session()->flash('error', 'Gagal hapus: Ada pesanan aktif di meja ini.');
            return;
        }
        $table->delete();
        session()->flash('message', 'Meja dihapus.');
    }

    public function toggleTableStatus($id)
    {
        $table = Table::findOrFail($id);
        $table->update(['is_active' => !$table->is_active]);
    }

    public function cancelTableEdit() { $this->resetTableForm(); }

    private function resetTableForm()
    {
        $this->reset(['tableId', 'table_area_id', 'table_number', 'capacity', 'qr_code', 'table_sort_order', 'is_table_active', 'isEditingTable']);
        $this->capacity = 4;
        $this->is_table_active = true;
    }

    // ==================== AREA CRUD ====================
    public function saveArea() {
        $this->validate($this->areaRules);
        $data = ['outlet_id'=>$this->outlet_id, 'name'=>$this->areaName, 'sort_order'=>$this->area_sort_order, 'is_active'=>$this->is_area_active];
        if ($this->areaId) TableArea::findOrFail($this->areaId)->update($data);
        else TableArea::create($data);
        $this->resetAreaForm();
    }
    public function editArea($id) {
        $a = TableArea::findOrFail($id);
        $this->areaId = $a->id; $this->areaName = $a->name; $this->area_sort_order = $a->sort_order; $this->is_area_active = (bool)$a->is_active; $this->isEditingArea = true;
    }
    public function deleteArea($id) {
        $a = TableArea::findOrFail($id);
        if($a->tables()->count()>0) return session()->flash('error','Area masih punya meja.');
        $a->delete();
    }
    public function cancelAreaEdit() { $this->resetAreaForm(); }
    private function resetAreaForm() { $this->reset(['areaId', 'areaName', 'area_sort_order', 'is_area_active', 'isEditingArea']); $this->is_area_active = true; }
    
    public function updatedSelectedOutlet($v) { $this->outlet_id = $v; $this->selectedArea = null; }
    public function getTableFormTitleProperty() { return $this->isEditingTable ? 'Edit Meja' : 'Tambah Meja'; }
    public function getAreaFormTitleProperty() { return $this->isEditingArea ? 'Edit Area' : 'Tambah Area'; }
}