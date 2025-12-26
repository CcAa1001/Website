<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Order;

class Dashboard extends Component
{
    // Pre-defined bakery tables
    public $tables = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'D1', 'D2'];

    public function render()
    {
        // Fetch data for the admin dashboard view
        $recentOrders = Order::orderBy('created_at', 'desc')->take(5)->get();
        $totalSales = Order::sum('total_amount');
        $activeOrders = Order::where('status', 'pending')->count();

        return view('livewire.dashboard', [
            'recentOrders' => $recentOrders,
            'totalSales' => $totalSales,
            'activeOrders' => $activeOrders
        ]);
    }
}