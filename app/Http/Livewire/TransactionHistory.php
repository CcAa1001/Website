<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class TransactionHistory extends Component
{
    use WithPagination;

    public $filterDateRange = 'today';
    public $filterStartDate;
    public $filterEndDate;
    public $filterStatus = 'all';
    public $filterPaymentStatus = 'all';
    public $search = '';
    public $selectedOrderId;
    public $showOrderDetail = false;
    
    // For expandable order items
    public $expandedOrderId = null;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['refund-completed' => 'refreshTransactions'];

    public function mount()
    {
        abort_unless(
        auth()->check() &&
        auth()->user()->hasRole(['supervisor', 'manager', 'admin']),
        403
    );
        $this->filterStartDate = today()->toDateString();
        $this->filterEndDate = today()->toDateString();
    }

    public function refreshTransactions()
    {
        $this->resetPage();
        $this->render();
    }

    public function toggleOrderItems($orderId)
    {
        $this->expandedOrderId = $this->expandedOrderId === $orderId ? null : $orderId;
    }

    public function render()
    {
        $user = auth()->user();

        // Query orders with items and refunds
        $query = Order::where('tenant_id', $user->tenant_id)
            ->whereIn('payment_status', ['paid', 'partial', 'refunded'])
            ->with([
                'table', 
                'items',
                'payments.paymentMethod',
                'refunds' => fn($q) => $q->where('status', 'completed')
                    ->with('items.orderItem') // Load refunded items
            ]);

        // Date filter
        if ($this->filterDateRange === 'custom') {
            if ($this->filterStartDate) {
                $query->whereDate('created_at', '>=', $this->filterStartDate);
            }
            if ($this->filterEndDate) {
                $query->whereDate('created_at', '<=', $this->filterEndDate);
            }
        } else {
            $date = $this->getDateRangeFilter();
            if ($this->filterDateRange === 'today') {
                $query->whereDate('created_at', $date);
            } else {
                $query->whereDate('created_at', '>=', $date);
            }
        }

        // Payment status filter
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

        $selectedOrder = $this->selectedOrderId 
            ? Order::with(['table', 'items', 'payments.paymentMethod'])->find($this->selectedOrderId)
            : null;

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

        // Net revenue (after refunds)
        $totalRevenue = DB::table('payments')
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('status', 'completed')
            ->sum('amount');

        // Total refunded
        $totalRefunded = DB::table('payments')
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('transaction_type', 'refund')
            ->where('status', 'completed')
            ->sum('amount') * -1;

        // Gross sales (before refunds)
        $grossSales = DB::table('payments')
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('transaction_type', 'payment')
            ->where('status', 'completed')
            ->sum('amount');

        // NEW: Calculate modal (working capital from cash payments)
        $cashPayments = DB::table('payments as p')
            ->join('payment_methods as pm', 'p.payment_method_id', '=', 'pm.id')
            ->where('p.tenant_id', $tenantId)
            ->whereDate('p.created_at', '>=', $startDate)
            ->whereDate('p.created_at', '<=', $endDate)
            ->where('p.status', 'completed')
            ->where('pm.payment_type', 'cash')
            ->sum('p.amount'); // Includes negative refunds

        return [
            'total_transactions' => Order::where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->whereIn('payment_status', ['paid', 'partial', 'refunded'])
                ->count(),
            
            'total_revenue' => $totalRevenue ?? 0,
            'gross_sales' => $grossSales ?? 0,
            'total_refunded' => $totalRefunded ?? 0,
            
            // NEW: Modal (cash in hand)
            'modal' => $cashPayments ?? 0,
            
            'pending_refunds' => Refund::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count() ?? 0,
        ];
    }

    // NEW: Check if item was refunded
    public function isItemRefunded($order, $orderItemId)
    {
        foreach ($order->refunds as $refund) {
            if ($refund->status !== 'completed') continue;
            
            foreach ($refund->items as $refundItem) {
                if ($refundItem->order_item_id === $orderItemId) {
                    return [
                        'refunded' => true,
                        'quantity' => $refundItem->quantity
                    ];
                }
            }
        }
        
        return ['refunded' => false, 'quantity' => 0];
    }

    // NEW: Get total refunded for this order
    public function getTotalRefunded($order)
    {
        return $order->refunds()
            ->where('status', 'completed')
            ->sum('total_refund_amount');
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
}