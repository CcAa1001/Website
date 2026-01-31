<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class TransactionHistory extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $dateRange = 'today'; // today, week, month, custom, all
    public $customStartDate;
    public $customEndDate;
    public $search = '';
    public $filterPaymentStatus = 'all';

    // Modal Receipt
    public $showReceiptModal = false;
    public $selectedOrder = null;
    
    // Expandable Rows
    public $expandedOrderId = null;

    public function mount()
    {
        if (!auth()->check()) return redirect()->route('login');
        
        $this->customStartDate = date('Y-m-d');
        $this->customEndDate = date('Y-m-d');
    }

    public function render()
    {
        $user = auth()->user();
        
        // Base Query
        $query = Order::where('tenant_id', $user->tenant_id)
            ->with(['items', 'table', 'payments', 'outlet', 'refunds']);

        // 1. Filter Date
        $this->applyDateFilter($query);

        // 2. Filter Status
        if ($this->filterPaymentStatus !== 'all') {
            $query->where('payment_status', $this->filterPaymentStatus);
        } else {
            // Default tampilkan yang paid/completed/refunded (bukan pending/unpaid)
            $query->whereIn('payment_status', ['paid', 'partial', 'refunded']);
        }

        // 3. Search
        if ($this->search) {
            $query->where(function($q) {
                $q->where('order_number', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%');
            });
        }

        // Clone query for stats
        $statsQuery = clone $query;
        
        // Get Paginated Data
        $transactions = $query->latest('created_at')->paginate(10);

        // Calculate Stats
        $stats = [
            'total_revenue' => $statsQuery->whereIn('status', ['paid', 'completed'])->sum('grand_total'),
            'total_orders'  => $statsQuery->count(),
            'total_refund'  => $statsQuery->where('status', 'refunded')->sum('grand_total'),
            'net_profit'    => $statsQuery->whereIn('status', ['paid', 'completed'])->sum('grand_total') * 0.3, // Estimasi 30%
            'modal'         => 0 // Bisa dihubungkan dengan cash flow nanti
        ];

        return view('livewire.transaction-history', [
            'transactions' => $transactions,
            'stats' => $stats
        ])->layout('layouts.app', ['activePage' => 'transactions', 'titlePage' => 'Laporan & Transaksi']);
    }

    // --- FILTERS ---

    public function setDateRange($range)
    {
        $this->dateRange = $range;
        $this->resetPage();
    }

    private function applyDateFilter($query)
    {
        switch ($this->dateRange) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'yesterday':
                $query->whereDate('created_at', Carbon::yesterday());
                break;
            case 'week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month);
                break;
            case 'custom':
                if ($this->customStartDate && $this->customEndDate) {
                    $query->whereDate('created_at', '>=', $this->customStartDate)
                          ->whereDate('created_at', '<=', $this->customEndDate);
                }
                break;
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterPaymentStatus', 'dateRange', 'customStartDate', 'customEndDate']);
        $this->dateRange = 'today';
    }

    // --- EXPORT CSV (Fitur dari LaporanManager lama) ---

    public function exportCSV()
    {
        $user = auth()->user();
        $query = Order::where('tenant_id', $user->tenant_id);
        $this->applyDateFilter($query);
        
        $orders = $query->get();

        $csv = "No. Order,Tanggal,Pelanggan,Meja,Status,Total\n";
        foreach ($orders as $o) {
            $csv .= "{$o->order_number},{$o->created_at},{$o->customer_name},{$o->table_id},{$o->payment_status},{$o->grand_total}\n";
        }

        return Response::streamDownload(fn() => print($csv), "Laporan_Transaksi_" . date('Ymd_His') . ".csv");
    }

    // --- ACTIONS ---

    public function showReceipt($id)
    {
        $this->selectedOrder = Order::with(['items.product', 'outlet', 'table', 'payments'])->find($id);
        $this->showReceiptModal = true;
    }

    public function closeReceipt()
    {
        $this->showReceiptModal = false;
        $this->selectedOrder = null;
    }

    public function toggleOrderItems($id)
    {
        $this->expandedOrderId = ($this->expandedOrderId === $id) ? null : $id;
    }

    public function processRefund($id)
    {
        $order = Order::find($id);
        if ($order && $order->status !== 'refunded') {
            DB::transaction(function () use ($order) {
                $order->update([
                    'status' => 'refunded',
                    'payment_status' => 'refunded'
                ]);
                
                // Buat record Refund
                Refund::create([
                    'tenant_id' => $order->tenant_id,
                    'order_id' => $order->id,
                    'amount' => $order->grand_total,
                    'reason' => 'Refund via Admin',
                    'status' => 'completed'
                ]);
            });

            $this->showReceiptModal = false;
            session()->flash('message', 'Transaksi berhasil di-refund.');
        }
    }

    // Helper untuk view
    public function getTotalRefunded($order)
    {
        return $order->refunds->where('status', 'completed')->sum('amount');
    }
}