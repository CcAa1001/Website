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
        $recentOrders = Order::orderBy('created_at', 'desc')->take(5)->get();
        $totalSales = Order::sum('grand_total');
        
        // This line will now work because the 'status' column exists
        $activeOrders = Order::where('status', 'pending')->count(); 

        return view('livewire.dashboard', [
            'recentOrders' => $recentOrders,
            'totalSales' => $totalSales,
            'activeOrders' => $activeOrders
        ]);
    }
}