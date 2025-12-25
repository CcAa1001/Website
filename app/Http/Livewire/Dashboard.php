<?php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component {
    public function render() {
        $userId = Auth::id();
        return view('livewire.dashboard', [
            'sales_today' => Order::where('user_id', $userId)->whereDate('created_at', now())->sum('total_amount') ?? 0,
            'total_customers' => Customer::where('user_id', $userId)->count(),
            'low_stock' => Product::where('user_id', $userId)->where('stock', '<', 5)->count(),
            'recent_orders' => Order::where('user_id', $userId)->with('customer')->latest()->take(5)->get()
        ]);
    }
}