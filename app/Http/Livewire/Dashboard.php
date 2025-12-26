<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $todaySales = Order::where('user_id', Auth::id())
            ->whereDate('created_at', today())
            ->sum('total_amount');

        $lowStockCount = Product::where('user_id', Auth::id())
            ->where('stock', '<', 10)
            ->count();

        $recentOrders = Order::where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.dashboard', [
            'todaySales' => $todaySales,
            'lowStockCount' => $lowStockCount,
            'recentOrders' => $recentOrders,
            'totalProducts' => Product::where('user_id', Auth::id())->count(),
        ]);
    }
}