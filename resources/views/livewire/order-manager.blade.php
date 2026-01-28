<div class="container-fluid py-4">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="material-icons">check_circle</i></span>
            <span class="alert-text">{{ session('message') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="material-icons">error</i></span>
            <span class="alert-text">{{ session('error') }}</span>
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
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Hari Ini</p>
                                <h5 class="font-weight-bolder mb-0">{{ $stats['total_today'] }}</h5>
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
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Menunggu</p>
                                <h5 class="font-weight-bolder mb-0 text-warning">{{ $stats['pending'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                <i class="material-icons opacity-10">schedule</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Diproses</p>
                                <h5 class="font-weight-bolder mb-0 text-info">{{ $stats['preparing'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="material-icons opacity-10">restaurant</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Pendapatan</p>
                                <h5 class="font-weight-bolder mb-0">Rp {{ number_format($stats['revenue_today'], 0, ',', '.') }}</h5>
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
    </div>

    {{-- Filters Bar --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <div class="row align-items-center g-2">
                        <div class="col-md-2">
                            <input type="text" class="form-control form-control-sm" placeholder="Cari order..." wire:model.live="search">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control form-control-sm" wire:model.live="filterDate">
                        </div>
                        <div class="col-md-2">
                            <select class="form-control form-control-sm" wire:model.live="filterStatus">
                                <option value="all">Semua Status</option>
                                <option value="pending">Menunggu</option>
                                <option value="confirmed">Dikonfirmasi</option>
                                <option value="preparing">Diproses</option>
                                <option value="ready">Siap</option>
                                <option value="served">Disajikan</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control form-control-sm" wire:model.live="filterTable">
                                <option value="">Semua Meja</option>
                                @foreach($tables as $table)
                                    <option value="{{ $table->id }}">Meja {{ $table->table_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control form-control-sm" wire:model.live="filterOrderSource">
                                <option value="all">Semua Sumber</option>
                                <option value="qr_scan">QR Scan</option>
                                <option value="pos">POS</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <button wire:click="clearFilters" class="btn btn-sm btn-outline-secondary">
                                <i class="material-icons text-xs">clear</i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Orders Grid --}}
    <div class="row">
        @forelse($orders as $order)
            <div class="col-lg-4 col-md-6 mb-4" wire:key="order-{{ $order->id }}">
                <div class="card h-100">
                    <div class="card-header pb-0 p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $order->order_number }}</h6>
                                <small class="text-muted">
                                    <i class="material-icons text-xs">schedule</i>
                                    {{ $order->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <span class="badge bg-gradient-{{ $this->getStatusBadgeColor($order->status) }}">
                                {{ $this->getStatusLabel($order->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        {{-- Table & Customer Info --}}
                        <div class="mb-3">
                            @if($order->table)
                                <div class="d-flex align-items-center mb-1">
                                    <i class="material-icons text-sm text-primary me-1">table_restaurant</i>
                                    <span class="text-sm font-weight-bold">Meja {{ $order->table->table_number }}</span>
                                </div>
                            @endif
                            @if($order->customer_name)
                                <div class="d-flex align-items-center">
                                    <i class="material-icons text-sm text-secondary me-1">person</i>
                                    <span class="text-sm">{{ $order->customer_name }}</span>
                                </div>
                            @endif
                            @if($order->order_source === 'qr_scan')
                                <span class="badge badge-sm bg-gradient-info mt-1">
                                    <i class="material-icons text-xs">qr_code</i> QR Order
                                </span>
                            @endif
                        </div>

                        {{-- Order Items --}}
                        <div class="mb-3">
                            <small class="text-uppercase text-secondary font-weight-bolder">Items:</small>
                            <ul class="list-unstyled mb-0">
                                @foreach($order->items->take(3) as $item)
                                    <li class="text-sm">
                                        <span class="badge badge-sm bg-gradient-dark">{{ $item->quantity }}x</span>
                                        {{ $item->product_name }}
                                        @if($item->variant_name)
                                            <small class="text-muted">({{ $item->variant_name }})</small>
                                        @endif
                                    </li>
                                @endforeach
                                @if($order->items->count() > 3)
                                    <li class="text-xs text-muted">+{{ $order->items->count() - 3 }} item lainnya</li>
                                @endif
                            </ul>
                        </div>

                        {{-- Total --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-sm font-weight-bold">Total:</span>
                            <span class="text-lg font-weight-bold text-primary">
                                Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                            </span>
                        </div>

                        {{-- Notes --}}
                        @if($order->notes)
                            <div class="alert alert-secondary py-2 mb-3">
                                <small><i class="material-icons text-xs">note</i> {{ $order->notes }}</small>
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="card-footer p-3 pt-0">
                        <div class="d-flex gap-1 flex-wrap">
                            @if($order->status === 'pending')
                                <button wire:click="updateOrderStatus('{{ $order->id }}', 'confirmed')" 
                                        class="btn btn-sm btn-info flex-grow-1">
                                    <i class="material-icons text-xs">check</i> Konfirmasi
                                </button>
                            @endif

                            @if($order->status === 'confirmed')
                                <button wire:click="updateOrderStatus('{{ $order->id }}', 'preparing')" 
                                        class="btn btn-sm btn-primary flex-grow-1">
                                    <i class="material-icons text-xs">restaurant</i> Proses
                                </button>
                            @endif

                            @if($order->status === 'preparing')
                                <button wire:click="markAllItemsReady('{{ $order->id }}')" 
                                        class="btn btn-sm btn-success flex-grow-1">
                                    <i class="material-icons text-xs">done_all</i> Siap
                                </button>
                            @endif

                            @if($order->status === 'ready')
                                <button wire:click="updateOrderStatus('{{ $order->id }}', 'served')" 
                                        class="btn btn-sm btn-secondary flex-grow-1">
                                    <i class="material-icons text-xs">room_service</i> Sajikan
                                </button>
                            @endif

                            <button wire:click="viewOrder('{{ $order->id }}')" 
                                    class="btn btn-sm btn-outline-secondary">
                                <i class="material-icons text-xs">visibility</i>
                            </button>

                            @if(in_array($order->status, ['pending', 'confirmed']))
                                <button wire:click="updateOrderStatus('{{ $order->id }}', 'cancelled')"
                                        onclick="return confirm('Yakin batalkan order ini?')"
                                        class="btn btn-sm btn-outline-danger">
                                    <i class="material-icons text-xs">close</i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="material-icons text-secondary" style="font-size: 72px;">receipt_long</i>
                        <h5 class="text-secondary mt-3">Tidak ada order</h5>
                        <p class="text-sm text-secondary">Order akan muncul di sini saat customer melakukan pemesanan</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="row mt-3">
        <div class="col-12">
            {{ $orders->links() }}
        </div>
    </div>
</div>

{{-- Order Detail Modal --}}
@if($showOrderDetail && $selectedOrder)
<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">{{ $selectedOrder->order_number }}</h5>
                    <small class="text-muted">{{ $selectedOrder->created_at->format('d M Y H:i') }}</small>
                </div>
                <button type="button" class="btn-close" wire:click="closeOrderDetail"></button>
            </div>
            <div class="modal-body">
                {{-- Order Info --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Meja:</strong> {{ $selectedOrder->table->table_number ?? '-' }}<br>
                        <strong>Customer:</strong> {{ $selectedOrder->customer_name ?? '-' }}<br>
                        <strong>Source:</strong> 
                        <span class="badge bg-{{ $selectedOrder->order_source === 'qr_scan' ? 'info' : 'secondary' }}">
                            {{ strtoupper($selectedOrder->order_source) }}
                        </span>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="badge bg-gradient-{{ $this->getStatusBadgeColor($selectedOrder->status) }} p-2">
                            {{ $this->getStatusLabel($selectedOrder->status) }}
                        </span>
                    </div>
                </div>

                {{-- Items Table --}}
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Subtotal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($selectedOrder->items as $item)
                            <tr>
                                <td>
                                    {{ $item->product_name }}
                                    @if($item->variant_name)
                                        <br><small class="text-muted">{{ $item->variant_name }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-end font-weight-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge badge-sm bg-{{ $item->kitchen_status === 'ready' ? 'success' : 'warning' }}">
                                        {{ ucfirst($item->kitchen_status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                            <td class="text-end">Rp {{ number_format($selectedOrder->subtotal, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Pajak:</strong></td>
                            <td class="text-end">Rp {{ number_format($selectedOrder->tax_amount, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong class="text-lg">TOTAL:</strong></td>
                            <td class="text-end"><strong class="text-lg">Rp {{ number_format($selectedOrder->grand_total, 0, ',', '.') }}</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                @if($selectedOrder->notes)
                    <div class="alert alert-secondary">
                        <strong>Catatan:</strong> {{ $selectedOrder->notes }}
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="closeOrderDetail">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

@push('js')
<script>
    // Auto-refresh every 30 seconds
    setInterval(() => {
        @if($autoRefresh)
            Livewire.dispatch('$refresh');
        @endif
    }, 30000);

    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(alert => {
            new bootstrap.Alert(alert).close();
        });
    }, 3000);
</script>
@endpush