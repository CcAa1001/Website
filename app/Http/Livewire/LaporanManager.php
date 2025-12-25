<?php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class LaporanManager extends Component {
    public $dateFilter = '';

    public function render() {
        $query = Order::where('user_id', Auth::id())->with('customer')->latest();
        
        if ($this->dateFilter) {
            $query->whereDate('created_at', $this->dateFilter);
        }

        return view('livewire.laporan-manager', [
            'orders' => $query->get(),
            'grand_total' => $query->sum('total_amount')
        ]);
    }
}