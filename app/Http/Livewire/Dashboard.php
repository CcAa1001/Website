<?php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component {
public function render() {
    return view('livewire.dashboard', [
        'sales_today' => Order::where('user_id', auth()->id())->whereDate('created_at', now())->sum('total_amount'),
        'total_customers' => Customer::where('user_id', auth()->id())->count(),
        'low_stock' => Product::where('user_id', auth()->id())->where('stock', '<', 5)->count(),
        'recent_orders' => Order::where('user_id', auth()->id())->latest()->take(5)->get()
    ]);
}
}