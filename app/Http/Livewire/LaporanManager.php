<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use Illuminate\Support\Facades\Response;

class LaporanManager extends Component
{
    use WithPagination;
    public $dateFrom, $dateTo;

    public function mount() {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function exportCSV() {
        $orders = Order::where('tenant_id', auth()->user()->tenant_id)
            ->whereBetween('created_at', [$this->dateFrom.' 00:00:00', $this->dateTo.' 23:59:59'])->get();

        $csv = "No. Order,Pelanggan,Total,Status,Tanggal\n";
        foreach($orders as $o) {
            $csv .= "{$o->order_number},{$o->customer_name},{$o->grand_total},{$o->status},{$o->created_at}\n";
        }

        return Response::streamDownload(fn() => print($csv), "Laporan_".date('Ymd').".csv");
    }

    public function render() {
        $orders = Order::where('tenant_id', auth()->user()->tenant_id)
            ->whereBetween('created_at', [$this->dateFrom.' 00:00:00', $this->dateTo.' 23:59:59'])
            ->latest()->paginate(10);
        return view('livewire.laporan-manager', ['orders' => $orders]);
    }
}