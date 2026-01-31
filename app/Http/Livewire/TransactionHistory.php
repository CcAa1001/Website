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
    public $dateRange = 'today'; // today, yesterday, week, month, custom
    public $customStartDate;
    public $customEndDate;
    public $search = '';
    public $filterPaymentStatus = 'all';

    // UI Control
    public $showReceiptModal = false;
    public $selectedOrder = null;
    public $expandedOrderId = null;

    // Listeners agar komponen lain bisa trigger refresh jika perlu
    protected $listeners = ['refreshTransactions' => '$refresh'];

    // Reset pagination saat filter berubah agar tidak error
    public function updatedDateRange() { $this->resetPage(); }
    public function updatedFilterPaymentStatus() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

    public function mount()
    {
        if (!auth()->check()) return redirect()->route('login');
        
        $this->customStartDate = date('Y-m-d');
        $this->customEndDate = date('Y-m-d');
    }

    public function render()
    {
        $user = auth()->user();
        
        // 1. QUERY DASAR (Sesuai Tenant)
        $query = Order::where('tenant_id', $user->tenant_id)
            ->with(['items.product', 'table', 'payments', 'outlet', 'refunds']); // Eager load product untuk cost_price

        // 2. FILTER TANGGAL (Logic Sentral)
        $query = $this->applyDateFilter($query);

        // 3. FILTER STATUS PEMBAYARAN
        if ($this->filterPaymentStatus !== 'all') {
            $query->where('payment_status', $this->filterPaymentStatus);
        } else {
            // Default: Tampilkan yang sudah dibayar atau refund (bukan pending/unpaid/draft)
            $query->whereIn('payment_status', ['paid', 'partial', 'refunded']);
        }

        // 4. SEARCH
        if ($this->search) {
            $query->where(function($q) {
                $q->where('order_number', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%');
            });
        }

        // ---------------------------------------------
        // PERHITUNGAN STATISTIK KEUANGAN (REAL FINANCE)
        // ---------------------------------------------
        
        // Clone query agar perhitungan statistik menggunakan filter tanggal yang SAMA persis dengan tabel
        // Kita gunakan get() untuk mengambil semua data (bukan paginate) khusus untuk hitung total
        $statsQuery = clone $query;
        $ordersForStats = $statsQuery->get(); 

        $totalRevenue = 0;
        $totalCost = 0;
        $totalRefund = 0;
        $totalTransactions = 0;

        foreach ($ordersForStats as $order) {
            // Hitung Revenue & Transaksi (Hanya yang Paid atau Partial)
            if (in_array($order->payment_status, ['paid', 'partial'])) {
                $totalRevenue += $order->grand_total;
                $totalTransactions++;

                // HITUNG MODAL / HPP (Cost of Goods Sold)
                foreach ($order->items as $item) {
                    // Prioritas 1: Ambil 'cost_price' yang tersimpan di table order_items (snapshot saat beli)
                    // Prioritas 2: Ambil 'cost_price' dari master product saat ini
                    // Prioritas 3: Default 0 jika tidak ada data
                    $costPerItem = $item->cost_price ?? ($item->product->cost_price ?? 0);
                    $totalCost += ($costPerItem * $item->quantity);
                }
            }

            // Hitung Refund (Jika status refunded)
            if ($order->payment_status === 'refunded') {
                $totalRefund += $order->grand_total;
            }
        }

        // Net Profit = Revenue - HPP (Modal)
        $netProfit = $totalRevenue - $totalCost;

        $stats = [
            'total_revenue' => $totalRevenue,
            'net_profit'    => $netProfit,
            'total_cost'    => $totalCost, // Total Modal
            'total_tx'      => $totalTransactions,
            'total_refund'  => $totalRefund
        ];

        // ---------------------------------------------

        return view('livewire.transaction-history', [
            'transactions' => $query->latest('created_at')->paginate(10),
            'stats' => $stats
        ])->layout('layouts.app', ['titlePage' => 'Laporan & Transaksi']);
    }

    // --- FILTERS ---

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
                // 7 Hari terakhir
                $query->whereBetween('created_at', [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->endOfDay()]);
                break;
            case 'month':
                // Bulan ini
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'custom':
                if ($this->customStartDate && $this->customEndDate) {
                    $query->whereDate('created_at', '>=', $this->customStartDate)
                          ->whereDate('created_at', '<=', $this->customEndDate);
                }
                break;
        }
        return $query;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterPaymentStatus', 'dateRange', 'customStartDate', 'customEndDate']);
        $this->dateRange = 'today';
        $this->resetPage();
    }

    // --- EXPORT CSV ---

    public function exportCSV()
    {
        $user = auth()->user();
        $query = Order::where('tenant_id', $user->tenant_id);
        $query = $this->applyDateFilter($query); // Gunakan filter tanggal yang sama
        
        $orders = $query->get();

        $csv = "No. Order,Tanggal,Pelanggan,Meja,Status,Total\n";
        foreach ($orders as $o) {
            $csv .= "{$o->order_number},{$o->created_at},{$o->customer_name},{$o->table_id},{$o->payment_status},{$o->grand_total}\n";
        }

        return Response::streamDownload(fn() => print($csv), "Laporan_Transaksi_" . date('Ymd_His') . ".csv");
    }

    // --- ACTIONS (Receipt, Refund, Expand) ---

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
        if ($order && $order->payment_status !== 'refunded') {
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
                    'reason' => 'Refund via Admin Panel',
                    'status' => 'completed'
                ]);
            });

            $this->showReceiptModal = false;
            session()->flash('message', 'Transaksi berhasil di-refund.');
        }
    }
}