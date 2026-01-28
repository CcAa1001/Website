<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;

class RefundHistory extends Component
{
    use WithPagination;

    public $filterDateRange = 'today';
    public $filterStartDate;
    public $filterEndDate;
    public $filterStatus = 'all'; // all, pending, approved, completed, rejected
    public $filterType = 'all'; // all, full, partial
    public $search = '';
    public $selectedRefundId;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->filterStartDate = today()->toDateString();
        $this->filterEndDate = today()->toDateString();
    }

    public function render()
    {
        $user = auth()->user();

        $query = Refund::where('tenant_id', $user->tenant_id)
            ->with([
                'order' => fn($q) => $q->select('id', 'order_number', 'customer_name', 'grand_total'),
                'requestedBy:id,name',
                'approvedBy:id,name',
                'items.orderItem'
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

        // Status filter
        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        // Type filter
        if ($this->filterType !== 'all') {
            $query->where('refund_type', $this->filterType);
        }

        // Search
        if ($this->search) {
            $query->whereHas('order', function($q) {
                $q->where('order_number', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%');
            });
        }

        $refunds = $query->latest('created_at')->paginate(15);

        $stats = $this->getStatistics($user->tenant_id);

        return view('livewire.refund-history', [
            'refunds' => $refunds,
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
            'total_refunded_amount' => Refund::where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->where('status', 'completed')
                ->sum('total_refund_amount'),
            
            'total_refunds_count' => Refund::where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->count(),
            
            'pending_refunds' => Refund::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count(),
            
            'approved_refunds' => Refund::where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->whereIn('status', ['approved', 'completed'])
                ->count(),
        ];
    }

    public function viewRefund($refundId)
    {
        $this->selectedRefundId = $refundId;
    }

    public function refunds() {
        return $this->hasMany(Refund::class);
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
        $this->reset(['search', 'filterStatus', 'filterType']);
        $this->filterDateRange = 'today';
        $this->filterStartDate = today()->toDateString();
        $this->filterEndDate = today()->toDateString();
        $this->resetPage();
    }
}