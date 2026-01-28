<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class LaporanManager extends Component
{
    use WithPagination;

    public $filterDateRange = 'today';
    public $filterStartDate;
    public $filterEndDate;
    public $filterPaymentStatus = 'all';
    public $search = '';
    
    // UI State
    public $expandedOrderId = null;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['refund-completed' => 'refreshTransactions'];

    public function mount() {
        $this->filterStartDate = today()->toDateString();
        $this->filterEndDate = today()->toDateString();
    }

    public function refreshTransactions() {
        $this->resetPage();
    }

    public function toggleOrderItems($orderId) {
        $this->expandedOrderId = ($this->expandedOrderId === $orderId) ? null : $orderId;
    }

    // --- FITUR EXPORT CSV ---
    public function exportCSV() {
        $data = $this->buildQuery()->get();
        $csvHeader = "Order No,Tanggal,Pelanggan,Meja,Status,Total,Refunded\n";
        $csvData = "";

        foreach($data as $row) {
            $refunded = $this->getTotalRefunded($row);
            
            // PERBAIKAN: Definisikan nama meja di variabel terpisah dulu
            $tableName = $row->table->name ?? '-'; 
            
            // Gunakan variabel $tableName di dalam string
            $csvData .= "{$row->order_number},{$row->created_at},{$row->customer_name},{$tableName},{$row->payment_status},{$row->grand_total},{$refunded}\n";
        }

        return Response::streamDownload(function() use ($csvHeader, $csvData) {
            echo $csvHeader . $csvData;
        }, 'Laporan-Penjualan-' . date('Y-m-d') . '.csv');
    }

    // Helper Functions
    public function buildQuery() {
        $user = auth()->user();
        $query = Order::where('tenant_id', $user->tenant_id)
            ->whereIn('payment_status', ['paid', 'partial', 'refunded'])
            ->with(['table', 'items', 'refunds']);

        if ($this->filterDateRange === 'custom') {
            $query->whereDate('created_at', '>=', $this->filterStartDate)
                  ->whereDate('created_at', '<=', $this->filterEndDate);
        } else {
            $date = match($this->filterDateRange) {
                'today' => today(),
                'yesterday' => today()->subDay(),
                'week' => today()->subDays(7),
                'month' => today()->subDays(30),
                default => today(),
            };
            if($this->filterDateRange === 'today') 
                $query->whereDate('created_at', $date);
            else 
                $query->whereDate('created_at', '>=', $date);
        }

        if ($this->filterPaymentStatus !== 'all') {
            $query->where('payment_status', $this->filterPaymentStatus);
        }

        if ($this->search) {
            $query->where('order_number', 'like', '%' . $this->search . '%');
        }

        return $query;
    }

    public function getTotalRefunded($order) {
        return $order->refunds->where('status', 'completed')->sum('total_refund_amount');
    }

    public function isItemRefunded($order, $itemId) {
        foreach ($order->refunds as $refund) {
            if ($refund->status !== 'completed') continue;
            foreach ($refund->items as $item) {
                if ($item->order_item_id == $itemId) return ['refunded' => true, 'quantity' => $item->quantity];
            }
        }
        return ['refunded' => false, 'quantity' => 0];
    }

    public function render()
    {
        $transactions = $this->buildQuery()->latest()->paginate(15);
        
        // Simple Stat Calculation
        $allOrders = $this->buildQuery()->get();
        $revenue = $allOrders->sum('grand_total');
        $refunded = 0;
        foreach($allOrders as $o) $refunded += $this->getTotalRefunded($o);

        return view('livewire.laporan-manager', [
            'transactions' => $transactions,
            'stats' => [
                'total_transactions' => $allOrders->count(),
                'total_revenue' => $revenue - $refunded,
                'total_refunded' => $refunded,
                'modal' => $revenue * 0.8 // Dummy modal
            ]
        ]);
    }
}