<div class="container-fluid py-4">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="material-icons text-sm">check_circle</i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="material-icons text-sm">error</i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Transaksi</p>
                                <h5 class="font-weight-bolder mb-0">{{ $stats['total_transactions'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="material-icons opacity-10">receipt_long</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Net Revenue --}}
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Pendapatan Bersih</p>
                                <h5 class="font-weight-bolder mb-0">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</h5>
                                <small class="text-muted">Setelah refund</small>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="material-icons opacity-10">payments</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- NEW: Modal (Cash in Hand) --}}
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Modal (Kas)</p>
                                <h5 class="font-weight-bolder mb-0 {{ $stats['modal'] < 0 ? 'text-danger' : 'text-info' }}">
                                    Rp {{ number_format($stats['modal'], 0, ',', '.') }}
                                </h5>
                                <small class="text-muted">Uang tunai</small>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="material-icons opacity-10">account_balance_wallet</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Total Refunded --}}
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Refund</p>
                                <h5 class="font-weight-bolder mb-0 text-danger">Rp {{ number_format($stats['total_refunded'], 0, ',', '.') }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                <i class="material-icons opacity-10">undo</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Cari..." wire:model.live="search">
                </div>
                <div class="col-md-2">
                    <select class="form-control form-control-sm" wire:model.live="filterDateRange">
                        <option value="today">Hari Ini</option>
                        <option value="yesterday">Kemarin</option>
                        <option value="week">7 Hari Terakhir</option>
                        <option value="month">30 Hari Terakhir</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                @if($filterDateRange === 'custom')
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" wire:model.live="filterStartDate">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" wire:model.live="filterEndDate">
                    </div>
                @endif
                <div class="col-md-2">
                    <select class="form-control form-control-sm" wire:model.live="filterPaymentStatus">
                        <option value="all">Semua Status</option>
                        <option value="paid">Paid</option>
                        <option value="partial">Partial</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button wire:click="clearFilters" class="btn btn-sm btn-outline-secondary">
                        <i class="material-icons text-xs">clear</i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="card">
        <div class="card-header pb-0">
            <h6>Riwayat Transaksi</h6>
        </div>
        <div class="card-body px-0 pb-2">
            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Order</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Waktu</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Meja</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                            <th class="text-secondary opacity-7"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $order)
                            @php
                                $totalRefunded = $this->getTotalRefunded($order);
                            @endphp
                            
                            {{-- Main Order Row --}}
                            <tr wire:key="order-{{ $order->id }}">
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">{{ $order->order_number }}</h6>
                                            <p class="text-xs text-secondary mb-0">{{ $order->customer_name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $order->created_at->format('d M Y') }}</p>
                                    <p class="text-xs text-secondary mb-0">{{ $order->created_at->format('H:i') }}</p>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">{{ $order->table->table_number ?? '-' }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    {{-- NEW: Show original and refunded amount --}}
                                    @if($totalRefunded > 0)
                                        <span class="text-secondary text-xs" style="text-decoration: line-through;">
                                            Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                        </span>
                                        <br>
                                        <span class="text-danger text-xs font-weight-bold">
                                            -Rp {{ number_format($totalRefunded, 0, ',', '.') }}
                                        </span>
                                        <br>
                                        <span class="text-success text-sm font-weight-bold">
                                            Rp {{ number_format($order->grand_total - $totalRefunded, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-secondary text-xs font-weight-bold">
                                            Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <span class="badge badge-sm bg-gradient-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'refunded' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <button wire:click="toggleOrderItems('{{ $order->id }}')" 
                                            class="btn btn-sm btn-outline-secondary mb-0 me-1"
                                            title="View Items">
                                        <i class="material-icons text-xs">
                                            {{ $expandedOrderId === $order->id ? 'expand_less' : 'expand_more' }}
                                        </i>
                                    </button>
                                    
                                    <button wire:click="viewOrder('{{ $order->id }}')" 
                                            class="btn btn-sm btn-outline-info mb-0 me-1"
                                            title="View Details">
                                        <i class="material-icons text-xs">visibility</i>
                                    </button>
                                    
                                    @if(in_array($order->payment_status, ['paid', 'partial']))
                                        <button 
                                            wire:click="$dispatch('openRefundModal', {orderId: '{{ $order->id }}'})"
                                            class="btn btn-sm btn-outline-danger mb-0"
                                            title="Refund">
                                            <i class="material-icons text-xs">undo</i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            
                            {{-- Expandable Items Row --}}
                            @if($expandedOrderId === $order->id)
                                <tr class="order-items-expanded">
                                    <td colspan="6" class="p-3 bg-light">
                                        <div class="ms-4">
                                            <h6 class="text-sm font-weight-bold mb-2">
                                                <i class="material-icons text-sm">receipt</i> Order Items
                                            </h6>
                                            <table class="table table-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-xs">Item</th>
                                                        <th class="text-xs text-center">Qty</th>
                                                        <th class="text-xs text-end">Price</th>
                                                        <th class="text-xs text-end">Subtotal</th>
                                                        <th class="text-xs text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($order->items as $item)
                                                        @php
                                                            $refundInfo = $this->isItemRefunded($order, $item->id);
                                                            $isRefunded = $refundInfo['refunded'];
                                                            $refundedQty = $refundInfo['quantity'];
                                                            $remainingQty = $item->quantity - $refundedQty;
                                                        @endphp
                                                        
                                                        <tr class="{{ $isRefunded && $refundedQty == $item->quantity ? 'refunded-item' : '' }}">
                                                            <td class="text-xs {{ $isRefunded && $refundedQty == $item->quantity ? 'text-decoration-line-through text-danger' : '' }}">
                                                                {{ $item->product_name }}
                                                                @if($item->variant_name)
                                                                    <small class="text-muted">({{ $item->variant_name }})</small>
                                                                @endif
                                                                @if($item->notes)
                                                                    <br><small class="text-info">
                                                                        <i class="fas fa-sticky-note"></i> {{ $item->notes }}
                                                                    </small>
                                                                @endif
                                                                
                                                                {{-- NEW: Show refund status --}}
                                                                @if($isRefunded)
                                                                    <br>
                                                                    <span class="badge badge-sm bg-danger">
                                                                        Refunded: {{ $refundedQty }} of {{ $item->quantity }}
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="text-xs text-center {{ $isRefunded && $refundedQty == $item->quantity ? 'text-decoration-line-through text-danger' : '' }}">
                                                                {{ $item->quantity }}
                                                                @if($isRefunded && $remainingQty > 0)
                                                                    <br><small class="text-success">({{ $remainingQty }} remaining)</small>
                                                                @endif
                                                            </td>
                                                            <td class="text-xs text-end {{ $isRefunded && $refundedQty == $item->quantity ? 'text-decoration-line-through text-danger' : '' }}">
                                                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                                            </td>
                                                            <td class="text-xs text-end font-weight-bold {{ $isRefunded && $refundedQty == $item->quantity ? 'text-decoration-line-through text-danger' : '' }}">
                                                                Rp {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}
                                                                @if($isRefunded && $remainingQty > 0)
                                                                    <br>
                                                                    <small class="text-success">
                                                                        Rp {{ number_format($item->unit_price * $remainingQty, 0, ',', '.') }}
                                                                    </small>
                                                                @endif
                                                            </td>
                                                            <td class="text-xs text-center">
                                                                @if($isRefunded && $refundedQty == $item->quantity)
                                                                    <span class="text-danger">
                                                                        <i class="material-icons text-sm">cancel</i>
                                                                    </span>
                                                                @elseif($isRefunded && $remainingQty > 0)
                                                                    <span class="text-warning">
                                                                        <i class="material-icons text-sm">indeterminate_check_box</i>
                                                                    </span>
                                                                @else
                                                                    <span class="text-success">
                                                                        <i class="material-icons text-sm">check_circle</i>
                                                                    </span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="3" class="text-end text-xs font-weight-bold">Subtotal:</td>
                                                        <td class="text-end text-xs font-weight-bold">
                                                            Rp {{ number_format($order->subtotal, 0, ',', '.') }}
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    @if($order->tax_amount > 0)
                                                        <tr>
                                                            <td colspan="3" class="text-end text-xs">Tax:</td>
                                                            <td class="text-end text-xs">
                                                                Rp {{ number_format($order->tax_amount, 0, ',', '.') }}
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                    @endif
                                                    @if($order->service_charge > 0)
                                                        <tr>
                                                            <td colspan="3" class="text-end text-xs">Service:</td>
                                                            <td class="text-end text-xs">
                                                                Rp {{ number_format($order->service_charge, 0, ',', '.') }}
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                    @endif
                                                    
                                                    {{-- NEW: Show refund info in totals --}}
                                                    @if($totalRefunded > 0)
                                                        <tr class="border-top">
                                                            <td colspan="3" class="text-end text-sm font-weight-bold">Grand Total (Original):</td>
                                                            <td class="text-end text-sm font-weight-bold text-muted text-decoration-line-through">
                                                                Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3" class="text-end text-sm font-weight-bold text-danger">Total Refunded:</td>
                                                            <td class="text-end text-sm font-weight-bold text-danger">
                                                                -Rp {{ number_format($totalRefunded, 0, ',', '.') }}
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3" class="text-end text-sm font-weight-bold">Net Total:</td>
                                                            <td class="text-end text-sm font-weight-bold text-success">
                                                                Rp {{ number_format($order->grand_total - $totalRefunded, 0, ',', '.') }}
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                    @else
                                                        <tr class="border-top">
                                                            <td colspan="3" class="text-end text-sm font-weight-bold">Grand Total:</td>
                                                            <td class="text-end text-sm font-weight-bold text-success">
                                                                Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                    @endif
                                                </tfoot>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="material-icons text-secondary" style="font-size: 48px;">receipt_long</i>
                                    <p class="text-secondary">Tidak ada transaksi</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $transactions->links() }}
    </div>

    {{-- Include RefundManager Component --}}
    @livewire('refund-manager')
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('refund-completed', () => {
            @this.$refresh();
        });
    });
</script>
@endpush

@push('styles')
<style>
.order-items-expanded {
    background-color: #f8f9fa;
}

.order-items-expanded table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.order-items-expanded thead {
    background: #e9ecef;
}

/* NEW: Refunded item styling */
.refunded-item {
    background-color: #ffe6e6;
}

.refunded-item td {
    opacity: 0.7;
}

.text-decoration-line-through {
    text-decoration: line-through !important;
    position: relative;
}

.text-decoration-line-through::after {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    width: 100%;
    height: 2px;
    background-color: #dc3545;
}

/* Badge styling for refunded items */
.badge.bg-danger {
    font-size: 10px;
    padding: 2px 6px;
}
</style>
@endpush