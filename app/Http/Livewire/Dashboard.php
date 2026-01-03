<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Table;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $salesData = [];
    public $categoryData = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $tenantId = auth()->user()->tenant_id;

        // 1. Data Penjualan 7 Hari (Group By Postgres Safe)
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

        // 2. Kategori Terlaris
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

        return view('livewire.dashboard', [
            'todaysEarnings' => Order::where('tenant_id', $tenantId)->whereDate('created_at', now())->whereIn('status', ['completed', 'paid', 'served'])->sum('grand_total'),
            'totalOrders' => Order::where('tenant_id', $tenantId)->whereDate('created_at', now())->count(),
            'activeOrders' => Order::where('tenant_id', $tenantId)->whereIn('status', ['pending', 'confirmed', 'preparing'])->count(),
            'newCustomers' => Customer::where('tenant_id', $tenantId)->whereMonth('created_at', now()->month)->count(),
            'tables' => Table::where('outlet_id', $outletId)->orderBy('table_number')->pluck('table_number'),
            'recentOrders' => Order::where('tenant_id', $tenantId)->latest()->take(5)->get(),
        ]);
    }
}