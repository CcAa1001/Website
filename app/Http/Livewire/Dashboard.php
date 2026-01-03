<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Table;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $totalSales = 0;
    public $todaysEarnings = 0;
    public $totalOrders = 0;
    public $activeOrders = 0;
    public $newCustomers = 0;
    public $tables = []; 
    public $recentOrders = []; // Variabel yang sebelumnya hilang
    
    public function mount()
    {
        $user = auth()->user();
        
        if (!$user || !$user->tenant_id) return;

        $tenantId = $user->tenant_id;
        $outletId = $user->outlet_id;

        // --- Statistik ---
        $this->todaysEarnings = Order::where('tenant_id', $tenantId)
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->whereDate('created_at', Carbon::today())
            ->whereIn('status', ['completed', 'paid', 'served'])
            ->sum('grand_total');

        $this->totalSales = Order::where('tenant_id', $tenantId)
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereIn('status', ['completed', 'paid', 'served'])
            ->sum('grand_total');

        $this->totalOrders = Order::where('tenant_id', $tenantId)
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->whereDate('created_at', Carbon::today())
            ->count();
            
        $this->activeOrders = Order::where('tenant_id', $tenantId)
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->count();

        $this->newCustomers = Customer::where('tenant_id', $tenantId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        // --- Data Meja (QR) ---
        if ($outletId) {
            $this->tables = Table::where('outlet_id', $outletId)
                ->where('is_active', true)
                ->orderBy('table_number', 'asc')
                ->pluck('table_number')
                ->toArray();
        }
        if (empty($this->tables)) $this->tables = ['1', '2', '3']; // Dummy fallback

        // --- Recent Orders (TAMBAHAN BARU) ---
        $this->recentOrders = Order::with('table') // Eager load relasi table
            ->where('tenant_id', $tenantId)
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->latest()
            ->take(5) // Ambil 5 terakhir
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}