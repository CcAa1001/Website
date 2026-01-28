<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\TableSession;
use App\Models\Outlet;
use Illuminate\Support\Facades\DB;

class OrderManager extends Component
{
    use WithPagination;

    // Filters
    public $filterStatus = 'all';
    public $filterTable = '';
    public $filterOutlet = '';
    public $filterOrderSource = 'all';
    public $filterDate = '';
    public $search = '';

    // UI State
    public $selectedOrderId;
    public $showOrderDetail = false;
    public $viewMode = 'grid'; // 'grid' or 'list'

    // Real-time polling
    public $autoRefresh = true;

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'filterStatus' => ['except' => 'all'],
        'filterTable' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->filterDate = today()->toDateString();
    }

    public function render()
    {
        $user = auth()->user();

        // Build query
        $query = Order::where('tenant_id', $user->tenant_id)
            ->with(['table', 'items', 'tableSession', 'outlet']);

        // Apply filters
        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterTable) {
            $query->where('table_id', $this->filterTable);
        }

        if ($this->filterOutlet) {
            $query->where('outlet_id', $this->filterOutlet);
        }

        if ($this->filterOrderSource !== 'all') {
            $query->where('order_source', $this->filterOrderSource);
        }

        if ($this->filterDate) {
            $query->whereDate('created_at', $this->filterDate);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('order_number', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%');
            });
        }

        $orders = $query->latest('created_at')
            ->paginate(12);

        // Get filter options
        $tables = Table::where('outlet_id', $user->outlet_id)
            ->orderBy('table_number')
            ->get();

        $outlets = Outlet::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->get();

        // Get selected order details
        $selectedOrder = null;
        if ($this->selectedOrderId) {
            $selectedOrder = Order::with(['table', 'items.product', 'tableSession', 'outlet'])
                ->find($this->selectedOrderId);
        }

        // Statistics
        $stats = [
            'total_today' => Order::where('tenant_id', $user->tenant_id)
                ->whereDate('created_at', today())
                ->count(),
            'pending' => Order::where('tenant_id', $user->tenant_id)
                ->where('status', 'pending')
                ->count(),
            'preparing' => Order::where('tenant_id', $user->tenant_id)
                ->whereIn('status', ['confirmed', 'preparing'])
                ->count(),
            'completed_today' => Order::where('tenant_id', $user->tenant_id)
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->count(),
            'revenue_today' => Order::where('tenant_id', $user->tenant_id)
                ->whereDate('created_at', today())
                ->whereIn('status', ['completed', 'served'])
                ->sum('grand_total'),
        ];

        return view('livewire.order-manager', [
            'orders' => $orders,
            'tables' => $tables,
            'outlets' => $outlets,
            'selectedOrder' => $selectedOrder,
            'stats' => $stats,
        ]);
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

    public function updateOrderStatus($orderId, $newStatus)
    {
        $order = Order::where('id', $orderId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready' => ['served'],
            'served' => ['completed'],
        ];

        if (!isset($validTransitions[$order->status]) || 
            !in_array($newStatus, $validTransitions[$order->status])) {
            session()->flash('error', 'Invalid status transition');
            return;
        }

        $updates = ['status' => $newStatus];

        switch ($newStatus) {
            case 'confirmed':
                $updates['confirmed_at'] = now();
                break;
            case 'preparing':
                $updates['confirmed_at'] = $updates['confirmed_at'] ?? now();
                break;
            case 'ready':
                $updates['prepared_at'] = now();
                break;
            case 'served':
                $updates['prepared_at'] = $updates['prepared_at'] ?? now();
                break;
            case 'completed':
                $updates['completed_at'] = now();
                $updates['payment_status'] = 'paid';
                // Close table session if exists
                if ($order->table_session_id) {
                    $session = TableSession::find($order->table_session_id);
                    if ($session && $session->isActive()) {
                        $session->close(auth()->id());
                    }
                }
                break;
            case 'cancelled':
                $updates['cancelled_at'] = now();
                $updates['cancellation_reason'] = 'Cancelled by staff';
                break;
        }

        $order->update($updates);

        session()->flash('message', 'Order status updated successfully!');
    }

    public function updateItemStatus($itemId, $newStatus)
    {
        $item = OrderItem::findOrFail($itemId);

        $validStatuses = ['pending', 'preparing', 'ready', 'served'];
        if (!in_array($newStatus, $validStatuses)) {
            return;
        }

        $updates = ['kitchen_status' => $newStatus];

        switch ($newStatus) {
            case 'preparing':
                $updates['kitchen_printed_at'] = $updates['kitchen_printed_at'] ?? now();
                break;
            case 'ready':
                $updates['prepared_at'] = now();
                break;
            case 'served':
                $updates['served_at'] = now();
                break;
        }

        $item->update($updates);

        // Auto-update order status based on items
        $order = $item->order;
        $allItemsReady = $order->items()->where('kitchen_status', '!=', 'ready')->count() === 0;
        if ($allItemsReady && $order->status === 'preparing') {
            $this->updateOrderStatus($order->id, 'ready');
        }

        session()->flash('message', 'Item status updated!');
    }

    public function markAllItemsReady($orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->items()->update([
            'kitchen_status' => 'ready',
            'prepared_at' => now(),
        ]);
        
        $this->updateOrderStatus($orderId, 'ready');
    }

    public function clearFilters()
    {
        $this->reset(['filterStatus', 'filterTable', 'filterOutlet', 'filterOrderSource', 'search']);
        $this->filterDate = today()->toDateString();
        $this->resetPage();
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    public function printOrder($orderId)
    {
        // TODO: Implement print functionality
        session()->flash('message', 'Print order #' . $orderId);
    }

    public function getStatusBadgeColor($status)
    {
        return match($status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'preparing' => 'primary',
            'ready' => 'success',
            'served' => 'secondary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabel($status)
    {
        return match($status) {
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'preparing' => 'Diproses',
            'ready' => 'Siap',
            'served' => 'Disajikan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($status),
        };
    }
}