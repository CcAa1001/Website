<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableSession;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $salesData = [];
    public $categoryData = [];
    public $showActiveOrders = true;

    public function mount()
    {
        $this->loadData();
    }

    #[On('refreshDashboard')]
    public function refresh()
    {
        $this->render();
    }

    public function loadData()
    {
        $tenantId = auth()->user()->tenant_id;

        // Sales Chart Data
        $sales = Order::where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(6))
            ->select(DB::raw('DATE(created_at) as sales_date'), DB::raw('SUM(grand_total) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'), 'asc')
            ->get();

        $this->salesData = [
            'labels' => $sales->pluck('sales_date')->map(fn($d) => date('D', strtotime($d)))->toArray(),
            'values' => $sales->pluck('total')->toArray(),
        ];

        // Top Categories Chart
        $categories = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.tenant_id', $tenantId)
            ->select('categories.name', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('categories.name')
            ->orderBy('total_qty', 'desc')
            ->limit(5)->get();

        $this->categoryData = [
            'labels' => $categories->pluck('name')->toArray(),
            'values' => $categories->pluck('total_qty')->toArray(),
        ];
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;
        $outletId = auth()->user()->outlet_id;

        // [FIX] Menampilkan SEMUA Order Aktif (POS + QR)
        // Saya menghapus where('order_source', 'qr_scan') agar order dari POS juga masuk
        $activeOrders = Order::where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->with(['table', 'items', 'customer'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Grouping status for Kanban
        $ordersByStatus = [
            'pending' => $activeOrders->where('status', 'pending')->values(),
            'confirmed' => $activeOrders->where('status', 'confirmed')->values(),
            'preparing' => $activeOrders->where('status', 'preparing')->values(),
            'ready' => $activeOrders->where('status', 'ready')->values(),
        ];

        // Active Tables
        $activeSessions = TableSession::where('tenant_id', $tenantId)
            ->where('outlet_id', $outletId)
            ->active()
            ->with(['table', 'orders'])
            ->get();

        // Stats
        $tableStats = [
            'total' => Table::where('outlet_id', $outletId)->count(),
            'occupied' => Table::where('outlet_id', $outletId)->where('status', 'occupied')->count(),
            'available' => Table::where('outlet_id', $outletId)->where('status', 'available')->count(),
        ];

        $todaysOrders = Order::where('tenant_id', $tenantId)
            ->whereDate('created_at', now())
            ->get();

        return view('livewire.dashboard-enhanced', [
            'todaysEarnings' => $todaysOrders->whereIn('status', ['completed', 'served'])->sum('grand_total'),
            'totalOrders' => $todaysOrders->count(),
            'activeOrdersCount' => $activeOrders->count(),
            'newCustomers' => Customer::where('tenant_id', $tenantId)->whereMonth('created_at', now()->month)->count(),
            'tables' => Table::where('outlet_id', $outletId)->orderBy('table_number')->pluck('table_number'),
            'activeOrders' => $activeOrders,
            'ordersByStatus' => $ordersByStatus,
            'activeSessions' => $activeSessions,
            'tableStats' => $tableStats,
        ])
        // [FIX] Mengirim variabel 'activePage' ke Layout agar sidebar menyala
        ->layout('layouts.app', ['activePage' => 'dashboard']);
    }

    public function quickUpdateStatus($orderId, $newStatus)
    {
        $order = Order::where('id', $orderId)->where('tenant_id', auth()->user()->tenant_id)->firstOrFail();
        $updates = ['status' => $newStatus];
        
        switch ($newStatus) {
            case 'confirmed': $updates['confirmed_at'] = now(); break;
            case 'preparing': $updates['confirmed_at'] = $updates['confirmed_at'] ?? now(); break;
            case 'ready': $updates['prepared_at'] = now(); break;
            case 'served': $updates['prepared_at'] = $updates['prepared_at'] ?? now(); break;
        }
        
        $order->update($updates);
        session()->flash('message', 'Status updated!');
    }
}