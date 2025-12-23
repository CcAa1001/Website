<?php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component {
public function render() {
    return view('livewire.dashboard', [
        'totalRevenue' => Order::where('user_id', Auth::id())->sum('total_amount'),
        'orderCount' => Order::where('user_id', Auth::id())->count(),
        'recentSales' => Order::where('user_id', Auth::id())->latest()->take(5)->get(),
        'lowStock' => Product::where('user_id', Auth::id())->where('stock', '<', 5)->count()
    ]);
}
}