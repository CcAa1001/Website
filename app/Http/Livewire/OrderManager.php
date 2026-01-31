<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\TableSession;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderManager extends Component
{
    use WithPagination;

    // Filter & Search
    public $statusFilter = 'active'; // active, completed
    public $search = '';
    public $autoRefresh = true;

    // Sidebar Control
    public $selectedOrder = null;
    public $isSidebarOpen = false;

    protected $listeners = ['refreshOrders' => '$refresh'];
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
    }

    // --- ACTIONS ---

    // 1. Pilih Order (Buka Sidebar)
    public function selectOrder($orderId)
    {
        $this->selectedOrder = Order::with(['items', 'table', 'outlet', 'user'])
            ->find($orderId);
        
        $this->isSidebarOpen = true;
    }

    // 2. Tutup Sidebar
    public function closeSidebar()
    {
        $this->isSidebarOpen = false;
        // Delay reset agar animasi tutup selesai dulu (opsional, di sini reset langsung)
        // $this->selectedOrder = null; 
    }

    // 3. Update Status Order
    public function updateOrderStatus($orderId, $status)
    {
        $order = Order::find($orderId);
        if(!$order) return;

        $updateData = ['status' => $status];
        
        // Timestamp logic
        if ($status == 'confirmed') $updateData['confirmed_at'] = now();
        if ($status == 'preparing') $updateData['prepared_at'] = now();
        if ($status == 'ready') $updateData['prepared_at'] = $order->prepared_at ?? now(); // Dapur selesai
        if ($status == 'served') $updateData['served_at'] = now();
        if ($status == 'completed') {
            $updateData['completed_at'] = now();
            $updateData['payment_status'] = 'paid';
        }
        if ($status == 'cancelled') {
            $updateData['cancelled_at'] = now();
        }

        $order->update($updateData);

        // Jika order yang diupdate sedang dibuka di sidebar, refresh datanya
        if ($this->selectedOrder && $this->selectedOrder->id == $orderId) {
            $this->selectedOrder = $order->fresh(['items', 'table']);
        }

        session()->flash('message', "Order #{$order->order_number} status: " . ucfirst($status));
    }

    public function render()
    {
        $user = auth()->user();

        $query = Order::where('tenant_id', $user->tenant_id)
            ->with(['table', 'items']);

        // Filter Status Logic
        if ($this->statusFilter === 'active') {
            // Tampilkan order yang belum selesai/batal
            $query->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'served']);
        } elseif ($this->statusFilter === 'completed') {
            $query->whereIn('status', ['completed', 'cancelled', 'refunded']);
        }

        // Search Logic
        if ($this->search) {
            $query->where(function($q) {
                $q->where('order_number', 'like', '%'.$this->search.'%')
                  ->orWhere('customer_name', 'like', '%'.$this->search.'%');
            });
        }

        // Stats Counter
        $stats = [
            'pending' => Order::where('tenant_id', $user->tenant_id)->where('status', 'pending')->count(),
            'kitchen' => Order::where('tenant_id', $user->tenant_id)->whereIn('status', ['confirmed', 'preparing'])->count(),
            'ready'   => Order::where('tenant_id', $user->tenant_id)->where('status', 'ready')->count(),
        ];

        // Sorting: Order lama di atas (FIFO) untuk Active, Order baru di atas untuk History
        $sortDirection = $this->statusFilter === 'active' ? 'asc' : 'desc';

        return view('livewire.order-manager', [
            'orders' => $query->orderBy('created_at', $sortDirection)->paginate(10),
            'stats' => $stats
        ])->layout('layouts.app', ['activePage' => 'orders', 'titlePage' => 'Pesanan Aktif']);
    }

    public function getStatusBadgeColor($status)
    {
        return match($status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'preparing' => 'primary',
            'ready' => 'success',
            'served' => 'secondary',
            'completed' => 'dark',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}