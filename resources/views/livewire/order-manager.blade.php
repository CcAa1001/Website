<div wire:poll.10s> {{-- ROOT ELEMENT UTAMA (WAJIB) --}}
    
    <div class="container-fluid py-4">
        
        {{-- Flash Messages --}}
        @if (session()->has('message'))
            <div class="alert alert-success text-white px-4 py-2 mb-4 role='alert'">
                <i class="material-icons text-sm me-2">check_circle</i> {{ session('message') }}
            </div>
        @endif

        {{-- 1. HEADER STATS --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-sm-6 mb-lg-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-warning shadow-warning text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">notifications_active</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Pesanan Baru</p>
                            <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-lg-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">soup_kitchen</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Di Dapur</p>
                            <h4 class="mb-0">{{ $stats['kitchen'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-lg-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">room_service</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Siap Saji</p>
                            <h4 class="mb-0">{{ $stats['ready'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                 {{-- Tombol Filter --}}
                 <div class="card h-100 d-flex align-items-center justify-content-center p-2 shadow-none border bg-transparent">
                     <div class="btn-group w-100" role="group">
                        <button type="button" wire:click="$set('statusFilter', 'active')" class="btn btn-sm {{ $statusFilter == 'active' ? 'btn-dark' : 'btn-outline-dark' }} mb-0">Aktif</button>
                        <button type="button" wire:click="$set('statusFilter', 'completed')" class="btn btn-sm {{ $statusFilter == 'completed' ? 'btn-dark' : 'btn-outline-dark' }} mb-0">Riwayat</button>
                    </div>
                 </div>
            </div>
        </div>

        {{-- 2. ORDER LIST (TABLE) --}}
        <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-4">
                    <h6 class="text-white mb-0">Daftar Pesanan {{ $statusFilter == 'active' ? 'Aktif' : 'Riwayat' }}</h6>
                    <div class="input-group input-group-outline bg-white rounded ps-2" style="width: 250px;">
                        <input type="text" class="form-control" placeholder="Cari Order / Meja..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0 table-hover">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Order ID</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Meja / Tamu</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Items</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Waktu</th>
                                <th class="text-secondary opacity-7"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr wire:click="selectOrder('{{ $order->id }}')" class="cursor-pointer transition-color hover-bg-light {{ $selectedOrder && $selectedOrder->id == $order->id ? 'bg-light' : '' }}">
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold text-primary">#{{ $order->order_number }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ ucfirst($order->order_type ?? 'dine-in') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm font-weight-bold mb-0">
                                            {{ $order->table->table_number ?? 'Bungkus' }}
                                        </p>
                                        <p class="text-xs text-secondary mb-0">{{ $order->customer_name ?? 'Guest' }}</p>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge bg-light text-dark border">
                                            {{ $order->items->sum('quantity') }} Item
                                        </span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @php
                                            $badgeColor = match($order->status) {
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'preparing' => 'primary',
                                                'ready' => 'success',
                                                'served' => 'secondary',
                                                'completed' => 'dark',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge badge-sm bg-gradient-{{ $badgeColor }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">
                                            {{ $order->created_at->format('H:i') }}
                                            <br>
                                            <small class="{{ $order->created_at->diffInMinutes() > 20 ? 'text-danger' : 'text-success' }}">
                                                {{ $order->created_at->diffForHumans(null, true, true) }}
                                            </small>
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button class="btn btn-link text-secondary mb-0">
                                            <i class="material-icons text-sm">chevron_right</i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-secondary">
                                        <i class="material-icons text-lg opacity-5">inbox</i> <br> Tidak ada pesanan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-3 mt-3">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>

        {{-- 3. SIDEBAR DETAIL (OFF-CANVAS STYLE) --}}
        {{-- Overlay Backdrop --}}
        <div class="sidebar-backdrop {{ $isSidebarOpen ? 'show' : '' }}" wire:click="closeSidebar"></div>

        {{-- Sidebar Panel --}}
        <div class="sidebar-panel shadow-lg {{ $isSidebarOpen ? 'show' : '' }}">
            @if($selectedOrder)
                <div class="sidebar-header p-3 bg-gradient-dark text-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-white mb-0">
                            {{ $selectedOrder->table->table_number ?? 'Takeaway' }}
                            <span class="text-xs font-weight-normal opacity-8 ms-2">#{{ $selectedOrder->order_number }}</span>
                        </h5>
                        <small class="opacity-8">{{ $selectedOrder->created_at->format('d M Y, H:i') }}</small>
                    </div>
                    <button wire:click="closeSidebar" class="btn btn-link text-white mb-0 p-0">
                        <i class="material-icons text-lg">close</i>
                    </button>
                </div>

                <div class="sidebar-body">
                    {{-- Status Bar --}}
                    <div class="p-3 bg-light border-bottom">
                        <label class="text-xs text-uppercase text-secondary font-weight-bolder mb-1">Status Pesanan</label>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="badge badge-lg bg-gradient-{{ $selectedOrder->status == 'pending' ? 'warning' : ($selectedOrder->status == 'ready' ? 'success' : 'info') }}">
                                {{ strtoupper($selectedOrder->status) }}
                            </span>
                            <span class="text-xs fw-bold">
                                <i class="far fa-clock"></i> {{ $selectedOrder->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    {{-- Item List --}}
                    <div class="p-3">
                        <label class="text-xs text-uppercase text-secondary font-weight-bolder mb-2">Daftar Menu</label>
                        
                        @foreach($selectedOrder->items as $item)
                        <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-2">
                            <div class="d-flex" style="max-width: 70%;">
                                <span class="badge bg-secondary me-2 rounded-circle" style="width:24px; height:24px; display:flex; align-items:center; justify-content:center;">{{ $item->quantity }}</span>
                                <div>
                                    <h6 class="text-sm text-dark mb-0">{{ $item->product_name }}</h6>
                                    @if($item->variant_name)
                                        <small class="text-xs text-muted d-block">{{ $item->variant_name }}</small>
                                    @endif
                                    @if($item->notes)
                                        <small class="text-xs text-danger fst-italic d-block mt-1">
                                            <i class="fas fa-sticky-note me-1"></i> {{ $item->notes }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <span class="text-sm font-weight-bold text-dark">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                        @endforeach
                    </div>

                    {{-- Payment Info --}}
                    <div class="p-3 bg-gray-100 mx-3 rounded mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Subtotal</small>
                            <small class="fw-bold">Rp {{ number_format($selectedOrder->subtotal, 0, ',', '.') }}</small>
                        </div>
                        @if($selectedOrder->tax_amount > 0)
                        <div class="d-flex justify-content-between mb-1">
                            <small>Tax</small>
                            <small>Rp {{ number_format($selectedOrder->tax_amount, 0, ',', '.') }}</small>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                            <h6 class="mb-0">TOTAL</h6>
                            <h6 class="mb-0 text-primary">Rp {{ number_format($selectedOrder->grand_total, 0, ',', '.') }}</h6>
                        </div>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="sidebar-footer p-3 bg-white border-top">
                    <label class="text-xs text-uppercase text-secondary font-weight-bolder mb-2 d-block">Aksi Cepat</label>
                    
                    <div class="row g-2">
                        @if($selectedOrder->status == 'pending')
                            <div class="col-6">
                                <button wire:click="updateOrderStatus('{{ $selectedOrder->id }}', 'cancelled')" 
                                    onclick="confirm('Batalkan pesanan ini?') || event.stopImmediatePropagation()"
                                    class="btn btn-outline-danger w-100 mb-0">Tolak</button>
                            </div>
                            <div class="col-6">
                                <button wire:click="updateOrderStatus('{{ $selectedOrder->id }}', 'confirmed')" 
                                    class="btn btn-info w-100 mb-0">Terima</button>
                            </div>
                        @endif

                        @if($selectedOrder->status == 'confirmed')
                            <div class="col-12">
                                <button wire:click="updateOrderStatus('{{ $selectedOrder->id }}', 'preparing')" 
                                    class="btn btn-primary w-100 mb-0">Mulai Masak</button>
                            </div>
                        @endif

                        @if($selectedOrder->status == 'preparing')
                            <div class="col-12">
                                <button wire:click="updateOrderStatus('{{ $selectedOrder->id }}', 'ready')" 
                                    class="btn btn-success w-100 mb-0">Pesanan Siap</button>
                            </div>
                        @endif

                        @if($selectedOrder->status == 'ready')
                            <div class="col-12">
                                <button wire:click="updateOrderStatus('{{ $selectedOrder->id }}', 'served')" 
                                    class="btn btn-secondary w-100 mb-0">Sajikan ke Meja</button>
                            </div>
                        @endif
                        
                        @if($selectedOrder->status == 'served')
                            <div class="col-12">
                                <button wire:click="updateOrderStatus('{{ $selectedOrder->id }}', 'completed')" 
                                    onclick="confirm('Selesaikan pesanan dan tandai LUNAS?') || event.stopImmediatePropagation()"
                                    class="btn btn-dark w-100 mb-0">Selesai & Bayar</button>
                            </div>
                        @endif

                        @if($selectedOrder->status == 'completed' || $selectedOrder->status == 'cancelled')
                            <div class="col-12 text-center text-muted py-2">
                                <small>Status Final: {{ ucfirst($selectedOrder->status) }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="d-flex align-items-center justify-content-center h-100">
                    <div class="spinner-border text-secondary" role="status"></div>
                </div>
            @endif
        </div>
    </div>

    {{-- 3. CSS KHUSUS (DIDALAM ROOT ELEMENT) --}}
    <style>
        .cursor-pointer { cursor: pointer; }
        .hover-bg-light:hover { background-color: #f8f9fa; }
        
        /* Sidebar Styles */
        .sidebar-panel {
            position: fixed;
            top: 0;
            right: -400px; /* Start hidden */
            width: 400px;
            height: 100vh;
            background: white;
            z-index: 1050;
            transition: right 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-panel.show {
            right: 0;
        }

        .sidebar-body {
            flex: 1;
            overflow-y: auto;
        }

        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.3);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease;
        }

        .sidebar-backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        /* Mobile Responsive Sidebar */
        @media (max-width: 576px) {
            .sidebar-panel {
                width: 100%;
                right: -100%;
            }
        }
    </style>
</div>