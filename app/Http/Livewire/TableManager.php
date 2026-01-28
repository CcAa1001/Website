<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Table;
use App\Models\TableArea;
use App\Models\Outlet;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableManager extends Component
{
    // Properties
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
    public $showQRModal = false;
    public $viewingTable = null; 
    public $qrCodeUrl;           
    public $qrCodeValue;         
    public $generatedQrSvg;      

    protected function rules() {
        return [
            'outlet_id' => 'required|exists:outlets,id',
            'table_number' => ['required', 'max:20', Rule::unique('tables')->where('outlet_id', $this->outlet_id)->ignore($this->tableId)],
            'qr_code' => ['nullable', 'string', 'max:255', Rule::unique('tables', 'qr_code')->ignore($this->tableId)],
            'capacity' => 'required|integer|min:1',
        ];
    }

    protected $areaRules = [
        'outlet_id' => 'required|exists:outlets,id',
        'areaName' => 'required|min:2|max:100',
    ];

    public function mount() {
        $user = auth()->user();
        $this->selectedOutlet = $user->outlet_id ?? Outlet::where('tenant_id', $user->tenant_id)->first()?->id;
        $this->outlet_id = $this->selectedOutlet;
    }

    public function render() {
        $user = auth()->user();
        $outlets = Outlet::where('tenant_id', $user->tenant_id)->orderBy('name')->get();
        $tables = collect();
        $areas = collect();

        if ($this->selectedOutlet) {
            $query = Table::where('outlet_id', $this->selectedOutlet)->with('tableArea');
            if ($this->selectedArea) $query->where('table_area_id', $this->selectedArea);
            $tables = $query->orderBy('table_area_id')->orderBy('table_number')->get();
            $areas = TableArea::where('outlet_id', $this->selectedOutlet)->withCount('tables')->orderBy('name')->get();
        }

        return view('livewire.table-manager', compact('outlets', 'tables', 'areas'))
            ->layout('layouts.app', ['activePage' => 'tables', 'titlePage' => 'Manajemen Meja']);
    }

    // [FIX] LOGIC QR CODE (Arahkan ke Route Login Meja)
    public function showQR($id)
    {
        $table = Table::with('tableArea')->findOrFail($id);
        $this->viewingTable = $table;
        
        // Gunakan QR Code custom jika ada, atau ID jika kosong
        $code = $table->qr_code ?: $table->id;
        
        // URL mengarah ke route 'table.login' yang kita buat di web.php
        $fullUrl = route('table.login', ['code' => $code]);
        
        $this->qrCodeUrl = $fullUrl;
        $this->qrCodeValue = $code;

        // Generate QR Code Size Besar (500px) agar tajam saat diprint/zoom
        if (class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            $this->generatedQrSvg = QrCode::size(500)
                ->color(0, 0, 0)
                ->backgroundColor(255, 255, 255)
                ->margin(1)
                ->generate($fullUrl);
        } else {
            $this->generatedQrSvg = 'Library Error';
        }

        $this->showQRModal = true;
    }

    public function regenerateQR($id) {
        $table = Table::findOrFail($id);
        $table->update(['qr_code' => Str::random(10)]); 
        if ($this->viewingTable && $this->viewingTable->id == $id) $this->showQR($id);
        session()->flash('message', 'QR Code di-reset!');
    }

    // [FIX] SAVE TABLE (Tambahkan Tenant ID)
    public function saveTable() {
        $this->validate();

        $data = [
            'outlet_id' => $this->outlet_id,
            'tenant_id' => auth()->user()->tenant_id, // [FIX] Wajib ada agar tidak error SQL
            'table_area_id' => $this->table_area_id,
            'table_number' => $this->table_number,
            'capacity' => $this->capacity,
            'qr_code' => $this->qr_code, 
            'is_active' => $this->is_table_active,
            'status' => 'available'
        ];

        if ($this->tableId) {
            Table::findOrFail($this->tableId)->update($data);
            session()->flash('message', 'Meja diperbarui!');
        } else {
            Table::create($data);
            session()->flash('message', 'Meja ditambahkan!');
        }
        
        $this->dispatch('close-modal');
        $this->resetTableForm();
    }

    // CRUD Methods Lainnya
    public function edit($id) {
        $table = Table::findOrFail($id);
        $this->tableId = $id;
        $this->table_number = $table->table_number;
        $this->table_area_id = $table->table_area_id;
        $this->capacity = $table->capacity;
        $this->qr_code = $table->qr_code;
        $this->isEditingTable = true;
        $this->dispatch('open-modal');
    }
    
    public function updateTable() { $this->saveTable(); }
    
    public function deleteTable($id) {
        Table::destroy($id);
        session()->flash('message', 'Meja dihapus.');
    }
    
    public function cancelTableEdit() { $this->resetTableForm(); }
    private function resetTableForm() { $this->reset(['tableId', 'table_area_id', 'table_number', 'capacity', 'qr_code', 'isEditingTable']); }
    
    // Area Methods
    public function saveArea() {
        $this->validate($this->areaRules);
        $data = ['outlet_id'=>$this->outlet_id, 'name'=>$this->areaName, 'tenant_id'=>auth()->user()->tenant_id];
        if ($this->areaId) TableArea::findOrFail($this->areaId)->update($data);
        else TableArea::create($data);
        $this->resetAreaForm();
    }
    public function editArea($id) {
        $a = TableArea::findOrFail($id);
        $this->areaId = $a->id; $this->areaName = $a->name; $this->isEditingArea = true;
    }
    public function deleteArea($id) {
        $a = TableArea::findOrFail($id);
        if($a->tables()->count()>0) return session()->flash('error','Area masih punya meja.');
        $a->delete();
    }
    public function cancelAreaEdit() { $this->resetAreaForm(); }
    private function resetAreaForm() { $this->reset(['areaId', 'areaName', 'isEditingArea']); }
    public function updatedSelectedOutlet($v) { $this->outlet_id = $v; $this->selectedArea = null; }
    public function getTableFormTitleProperty() { return $this->isEditingTable ? 'Edit Meja' : 'Tambah Meja'; }
    public function getAreaFormTitleProperty() { return $this->isEditingArea ? 'Edit Area' : 'Tambah Area'; }
}