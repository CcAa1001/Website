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
    // Properties Table
    public $tableId;
    public $outlet_id;
    public $table_area_id;
    public $table_number;
    public $capacity = 4;
    public $qr_code;
    public $is_table_active = true;
    public $isEditingTable = false;

    // UI State
    public $selectedOutlet;
    public $selectedArea;
    public $showQRModal = false;
    public $viewingTable = null; 
    public $qrCodeUrl;          
    public $generatedQrSvg;       

    // Listeners (Optional in V3 but kept for compatibility)
    protected $listeners = ['refreshComponent' => '$refresh'];

    protected function rules() {
        return [
            'outlet_id' => 'required|exists:outlets,id',
            'table_number' => [
                'required', 
                'max:20', 
                Rule::unique('tables')
                    ->where('outlet_id', $this->outlet_id)
                    ->whereNull('deleted_at') // Handle soft deletes
                    ->ignore($this->tableId)
            ],
            'qr_code' => [
                'nullable', 
                'string', 
                'max:255', 
                Rule::unique('tables', 'qr_code')
                    ->whereNull('deleted_at')
                    ->ignore($this->tableId)
            ],
            'capacity' => 'required|integer|min:1',
            'table_area_id' => 'nullable|exists:table_areas,id',
        ];
    }

    public function mount() {
        $user = auth()->user();
        // Set default outlet
        $this->selectedOutlet = $user->outlet_id ?? Outlet::where('tenant_id', $user->tenant_id)->first()?->id;
        $this->outlet_id = $this->selectedOutlet;
    }

    public function render() {
        $user = auth()->user();
        
        $outlets = Outlet::where('tenant_id', $user->tenant_id)->orderBy('name')->get();
        
        $tables = collect();
        $areas = collect();

        if ($this->selectedOutlet) {
            $areas = TableArea::where('outlet_id', $this->selectedOutlet)
                              ->orderBy('name')
                              ->get();

            $query = Table::where('outlet_id', $this->selectedOutlet)
                          ->with('tableArea');
            
            if ($this->selectedArea) {
                $query->where('table_area_id', $this->selectedArea);
            }
            
            $tables = $query->orderBy('table_area_id')->orderBy('table_number')->get();
        }

        return view('livewire.table-manager', compact('outlets', 'tables', 'areas'))
            ->layout('layouts.app', ['activePage' => 'tables', 'titlePage' => 'Manajemen Meja']);
    }

    // --- CREATE & UPDATE ---

    public function create()
    {
        $this->resetTableForm();
        $this->isEditingTable = false;
        $this->outlet_id = $this->selectedOutlet; // Pastikan outlet terpilih
        
        // [FIXED] Ganti dispatchBrowserEvent menjadi dispatch
        $this->dispatch('open-modal-form'); 
    }

    public function edit($id) {
        $table = Table::findOrFail($id);
        $this->tableId = $id;
        $this->outlet_id = $table->outlet_id;
        $this->table_number = $table->table_number;
        $this->table_area_id = $table->table_area_id;
        $this->capacity = $table->capacity;
        $this->qr_code = $table->qr_code;
        $this->is_table_active = $table->is_active;
        
        $this->isEditingTable = true;
        
        // [FIXED] Ganti dispatchBrowserEvent menjadi dispatch
        $this->dispatch('open-modal-form'); 
    }

    public function saveTable() {
        $this->validate();

        // Auto Generate QR Code jika kosong
        if (empty($this->qr_code)) {
            $outletCode = Outlet::find($this->outlet_id)->code ?? 'OUT';
            $this->qr_code = 'QR-' . $outletCode . '-' . str_replace(' ', '', $this->table_number) . '-' . Str::random(4);
        }

        $data = [
            'tenant_id' => auth()->user()->tenant_id, // Penting!
            'outlet_id' => $this->outlet_id,
            'table_area_id' => $this->table_area_id ?: null,
            'table_number' => $this->table_number,
            'capacity' => $this->capacity,
            'qr_code' => $this->qr_code, 
            'is_active' => $this->is_table_active,
            'status' => 'available'
        ];

        if ($this->tableId) {
            Table::findOrFail($this->tableId)->update($data);
            session()->flash('message', 'Meja berhasil diperbarui!');
        } else {
            Table::create($data);
            session()->flash('message', 'Meja baru berhasil ditambahkan!');
        }
        
        // [FIXED] Ganti dispatchBrowserEvent menjadi dispatch
        $this->dispatch('close-modal-form'); 
        $this->resetTableForm();
    }

    // --- DELETE ---

    public function deleteTable($id) {
        $table = Table::find($id);
        if ($table) {
            $table->delete();
            session()->flash('message', 'Meja berhasil dihapus.');
        }
    }

    // --- QR CODE VIEWER ---

    public function showQR($id)
    {
        $table = Table::with('tableArea')->findOrFail($id);
        $this->viewingTable = $table;
        
        $code = $table->qr_code ?: $table->id;
        // PENTING: Gunakan route name yang sudah kita perbaiki
        $fullUrl = route('table.login', ['code' => $code]);
        
        $this->qrCodeUrl = $fullUrl;

        // Generate SVG untuk preview tajam
        if (class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            $this->generatedQrSvg = QrCode::size(300)
                ->margin(2)
                ->generate($fullUrl);
        }

        $this->showQRModal = true;
    }

    // --- HELPERS ---

    public function updatedSelectedOutlet($val) {
        $this->outlet_id = $val;
        $this->selectedArea = null;
    }

    private function resetTableForm() { 
        $this->reset(['tableId', 'table_area_id', 'table_number', 'capacity', 'qr_code', 'isEditingTable']); 
        $this->is_table_active = true;
    }
}