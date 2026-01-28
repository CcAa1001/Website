<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;

class TransactionHistory extends Component
{
    use WithPagination;

    // Filters
    public $filterDateRange = 'today';
    public $filterStartDate;
    public $filterEndDate;
    public $filterStatus = 'all';
    public $filterPaymentStatus = 'all';
    public $search = '';

    // UI State
    public $selectedOrderId;
    public $showOrderDetail = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->filterStartDate = today()->toDateString();
        $this->filterEndDate = today()->toDateString();
    }

    public function render()
    {
        $user = auth()->user();

        // Build query - only show completed/paid orders
        $query = Order::where('tenant_id', $user->tenant_id)
            ->whereIn('status', ['completed', 'served'])
            ->whereIn('payment_status', ['paid', 'partial', 'refunded'])
            ->with(['table', 'items', 'payments', 'refunds']);

        // Apply date filter
        if ($this->filterDateRange === 'custom') {
            if ($this->filterStartDate) {
                $query->whereDate('created_at', '>=', $this->filterStartDate);
            }
            if ($this->filterEndDate) {
                $query->whereDate('created_at', '<=', $this->filterEndDate);
            }
        } else {
            $query->whereDate('created_at', $this->getDateRangeFilter());
        }

        // Apply status filters
        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterPaymentStatus !== 'all') {
            $query->where('payment_status', $this->filterPaymentStatus);
        }

        // Search
        if ($this->search) {
            $query->where(function($q) {
                $q->where('order_number', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%');
            });
        }

        $transactions = $query->latest('created_at')->paginate(15);

        // Get selected order details
        $selectedOrder = null;
        if ($this->selectedOrderId) {
            $selectedOrder = Order::with(['table', 'items.product', 'payments.paymentMethod', 'refunds'])
                ->find($this->selectedOrderId);
        }

        // Statistics
        $stats = $this->getStatistics($user->tenant_id);

        return view('livewire.transaction-history', [
            'transactions' => $transactions,
            'selectedOrder' => $selectedOrder,
            'stats' => $stats,
        ]);
    }

    protected function getDateRangeFilter()
    {
        return match($this->filterDateRange) {
            'today' => today(),
            'yesterday' => today()->subDay(),
            'week' => today()->subDays(7),
            'month' => today()->subDays(30),
            default => today(),
        };
    }

    protected function getStatistics($tenantId)
    {
        $startDate = $this->filterDateRange === 'custom' && $this->filterStartDate
            ? $this->filterStartDate
            : $this->getDateRangeFilter();

        $endDate = $this->filterDateRange === 'custom' && $this->filterEndDate
            ? $this->filterEndDate
            : today();

        return [
            'total_transactions' => Order::where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->whereIn('payment_status', ['paid', 'refunded'])
                ->count(),
            
            'total_revenue' => Order::where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->where('payment_status', 'paid')
                ->sum('grand_total'),
            
            'total_refunded' => Refund::where('tenant_id', $tenantId)
                ->whereDate('requested_at', '>=', $startDate)
                ->whereDate('requested_at', '<=', $endDate)
                ->where('status', 'completed')
                ->sum('total_refund_amount'),
            
            'pending_refunds' => Refund::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count(),
        ];
    }

    public function viewOrder($orderId)
    {
        $this->selectedOrderId = $orderId;
        $this->showOrderDetail = true;
    }

    public function closeOrderDetail()
    {
        $this->showOrderDetail = false;
        $this->selectedOrderId = null;
    }

    public function setDateRange($range)
    {
        $this->filterDateRange = $range;
        
        if ($range !== 'custom') {
            $this->filterStartDate = $this->getDateRangeFilter()->toDateString();
            $this->filterEndDate = today()->toDateString();
        }
        
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterStatus', 'filterPaymentStatus']);
        $this->filterDateRange = 'today';
        $this->filterStartDate = today()->toDateString();
        $this->filterEndDate = today()->toDateString();
        $this->resetPage();
    }

    public function exportPDF()
    {
        // TODO: Implement PDF export
        session()->flash('message', 'PDF export feature coming soon');
    }

    public function exportExcel()
    {
        // TODO: Implement Excel export
        session()->flash('message', 'Excel export feature coming soon');
    }
}