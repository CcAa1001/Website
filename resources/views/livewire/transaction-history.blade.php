<div> {{-- ROOT ELEMENT --}}
    <div class="container-fluid py-4">

        {{-- Notifikasi --}}
        @if (session()->has('message'))
            <div class="alert alert-success text-white px-4 py-2 mb-4 role='alert'">
                <i class="material-icons text-sm me-2">check_circle</i> {{ session('message') }}
            </div>
        @endif

        {{-- 1. BOX STATISTIK --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">payments</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Total Pendapatan</p>
                            <h4 class="mb-0">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">Gross</span> Revenue</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">savings</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Laba Bersih (Est)</p>
                            <h4 class="mb-0">Rp {{ number_format($stats['net_profit'], 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <p class="mb-0 text-sm">Estimasi Profit (30%)</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">receipt_long</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Transaksi</p>
                            <h4 class="mb-0">{{ $stats['total_orders'] }}</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <p class="mb-0 text-sm">Pesanan Selesai</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-danger shadow-danger text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">undo</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Refund</p>
                            <h4 class="mb-0">Rp {{ number_format($stats['total_refund'], 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <p class="mb-0 text-sm">Total Pengembalian</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. FILTER & KONTROL --}}
        <div class="card mb-4">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="input-group input-group-outline">
                            <label class="form-label">Cari Order / Pelanggan...</label>
                            <input type="text" class="form-control" wire:model.live.debounce.500ms="search">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-static">
                            <select class="form-control" wire:model.live="dateRange">
                                <option value="today">Hari Ini</option>
                                <option value="yesterday">Kemarin</option>
                                <option value="week">Minggu Ini</option>
                                <option value="month">Bulan Ini</option>
                                <option value="custom">Pilih Tanggal...</option>
                                <option value="all">Semua Data</option>
                            </select>
                        </div>
                    </div>
                    
                    @if($dateRange === 'custom')
                    <div class="col-md-2">
                        <div class="input-group input-group-static">
                            <input type="date" class="form-control" wire:model.live="customStartDate">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group input-group-static">
                            <input type="date" class="form-control" wire:model.live="customEndDate">
                        </div>
                    </div>
                    @endif

                    <div class="col-md-2 text-end ms-auto">
                        <button class="btn btn-success btn-sm mb-0" wire:click="exportCSV">
                            <i class="material-icons text-sm">download</i> Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. TABEL TRANSAKSI --}}
        <div class="card">
            <div class="card-header pb-0">
                <div class="row">
                    <div class="col-lg-6 col-7">
                        <h6>Daftar Transaksi</h6>
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pb-2">
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID Order</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pelanggan</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Waktu</th>
                                <th class="text-secondary opacity-7"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $order)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm text-primary">{{ $order->order_number }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $order->customer_name }}</p>
                                        <p class="text-xs text-secondary mb-0">Meja: {{ $order->table->table_number ?? 'Takeaway' }}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm bg-gradient-{{ $order->payment_status == 'paid' ? 'success' : ($order->payment_status == 'refunded' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <button class="btn btn-link text-secondary mb-0" wire:click="showReceipt('{{ $order->id }}')">
                                            <i class="material-icons text-xs">receipt_long</i> Detail
                                        </button>
                                        <button class="btn btn-link text-secondary mb-0" wire:click="toggleOrderItems('{{ $order->id }}')">
                                            <i class="material-icons text-xs">{{ $expandedOrderId === $order->id ? 'expand_less' : 'expand_more' }}</i> Item
                                        </button>
                                    </td>
                                </tr>

                                {{-- Expanded Items Row --}}
                                @if($expandedOrderId === $order->id)
                                    <tr>
                                        <td colspan="6" class="bg-gray-100 p-3">
                                            <div class="row">
                                                <div class="col-12">
                                                    <h6 class="text-xs font-weight-bold mb-2">Item Pesanan:</h6>
                                                    <ul class="list-group">
                                                        @foreach($order->items as $item)
                                                            <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="d-flex flex-column">
                                                                        <h6 class="mb-1 text-dark text-sm">{{ $item->product_name }}</h6>
                                                                        <span class="text-xs">{{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex align-items-center text-success text-gradient text-sm font-weight-bold">
                                                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary">Belum ada data transaksi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-3 mt-3">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>

        {{-- 4. MODAL STRUK (RECEIPT) --}}
        @if($showReceiptModal && $selectedOrder)
        <div class="fixed-top w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="bg-white shadow-lg" style="width: 350px; max-height: 90vh; overflow-y: auto; font-family: 'Courier New', monospace;">
                <div class="p-4 pb-0 text-center">
                    <h5 class="fw-bold mb-0">{{ $selectedOrder->outlet->name ?? 'Bakery Name' }}</h5>
                    <p class="text-xs text-muted">Receipt #{{ $selectedOrder->order_number }}</p>
                    <hr class="border-dashed">
                </div>
                
                <div class="px-4 py-2">
                    @foreach($selectedOrder->items as $item)
                    <div class="d-flex justify-content-between text-xs mb-1">
                        <span>{{ $item->quantity }}x {{ $item->product_name }}</span>
                        <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                    <hr class="border-dashed my-2">
                    
                    <div class="d-flex justify-content-between text-sm fw-bold">
                        <span>TOTAL</span>
                        <span>Rp {{ number_format($selectedOrder->grand_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-xs mt-1 text-muted">
                        <span>Status</span>
                        <span class="text-uppercase">{{ $selectedOrder->payment_status }}</span>
                    </div>
                </div>

                <div class="p-4 pt-0 text-center">
                    <hr class="border-dashed mb-3">
                    <button class="btn btn-dark btn-sm w-100 mb-2" onclick="window.print()">
                        <i class="material-icons text-xs me-1">print</i> Cetak
                    </button>
                    
                    @if($selectedOrder->payment_status !== 'refunded')
                    <button class="btn btn-outline-danger btn-sm w-100 mb-2" 
                            onclick="confirm('Refund transaksi ini?') || event.stopImmediatePropagation()"
                            wire:click="processRefund('{{ $selectedOrder->id }}')">
                        Refund Transaksi
                    </button>
                    @else
                    <button class="btn btn-secondary btn-sm w-100 mb-2" disabled>Sudah Refund</button>
                    @endif

                    <button wire:click="closeReceipt" class="btn btn-link text-secondary btn-sm w-100">Tutup</button>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- STYLE --}}
    <style>
        .border-dashed { border-top: 1px dashed #ccc; }
        .bg-gray-100 { background-color: #f8f9fa !important; }
    </style>
</div>