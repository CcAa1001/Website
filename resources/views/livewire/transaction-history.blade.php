<div wire:poll.10s> {{-- FITUR REALTIME: Update otomatis setiap 10 detik --}}
    
    <div class="container-fluid py-4">

        {{-- Flash Messages --}}
        @if (session()->has('message'))
            <div class="alert alert-success text-white px-4 py-2 mb-4 role='alert'">
                <i class="material-icons text-sm me-2">check_circle</i> {{ session('message') }}
            </div>
        @endif

        {{-- 1. BOX STATISTIK (KEUANGAN REAL) --}}
        <div class="row mb-4">
            {{-- REVENUE (KOTOR) --}}
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
                        <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">Gross</span> Revenue (Kotor)</p>
                    </div>
                </div>
            </div>

            {{-- NET PROFIT (BERSIH) --}}
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">savings</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Laba Bersih</p>
                            <h4 class="mb-0">Rp {{ number_format($stats['net_profit'], 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0 text-xs text-secondary">
                                Modal (HPP): <span class="text-dark font-weight-bold">Rp {{ number_format($stats['total_cost'], 0, ',', '.') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TRANSAKSI --}}
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">receipt_long</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Transaksi Sukses</p>
                            <h4 class="mb-0">{{ $stats['total_tx'] }}</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <p class="mb-0 text-sm">Pesanan (Paid & Partial)</p>
                    </div>
                </div>
            </div>

            {{-- REFUND --}}
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-danger shadow-danger text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">undo</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Refund / Batal</p>
                            <h4 class="mb-0">Rp {{ number_format($stats['total_refund'], 0, ',', '.') }}</h4>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-3">
                        <p class="mb-0 text-sm">Total Pengembalian Dana</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. FILTER & KONTROL --}}
        <div class="card mb-4">
            <div class="card-body p-3">
                <div class="row align-items-center g-3">
                    
                    {{-- Search --}}
                    <div class="col-md-3">
                        <div class="input-group input-group-outline {{ $search ? 'is-filled' : '' }}">
                            <label class="form-label">Cari Order / Pelanggan...</label>
                            <input type="text" class="form-control" wire:model.live.debounce.500ms="search">
                        </div>
                    </div>

                    {{-- Date Range Filter --}}
                    <div class="col-md-2">
                        <div class="input-group input-group-static">
                            <select class="form-control" wire:model.live="dateRange">
                                <option value="today">Hari Ini</option>
                                <option value="yesterday">Kemarin</option>
                                <option value="week">7 Hari Terakhir</option>
                                <option value="month">Bulan Ini</option>
                                <option value="custom">Pilih Tanggal...</option>
                            </select>
                        </div>
                    </div>

                    {{-- Custom Date Inputs (Muncul jika pilih 'custom') --}}
                    @if($dateRange === 'custom')
                    <div class="col-md-2">
                        <div class="input-group input-group-static">
                            <input type="date" class="form-control" wire:model.live="customStartDate" title="Tanggal Mulai">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group input-group-static">
                            <input type="date" class="form-control" wire:model.live="customEndDate" title="Tanggal Selesai">
                        </div>
                    </div>
                    @endif

                    {{-- Status Filter --}}
                    <div class="col-md-2">
                        <div class="input-group input-group-static">
                            <select class="form-control" wire:model.live="filterPaymentStatus">
                                <option value="all">Semua Status</option>
                                <option value="paid">Lunas (Paid)</option>
                                <option value="partial">Sebagian (Partial)</option>
                                <option value="refunded">Refund</option>
                            </select>
                        </div>
                    </div>

                    {{-- Export Button --}}
                    <div class="col-md-auto ms-auto">
                        <button class="btn btn-success btn-sm mb-0 w-100" wire:click="exportCSV">
                            <i class="material-icons text-sm me-1">download</i> Export
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
                        <h6>Riwayat Transaksi</h6>
                        <p class="text-sm mb-0">Menampilkan data sesuai filter tanggal.</p>
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">Order ID</th>
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
                                        <div class="d-flex px-3 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm font-weight-bold text-primary">{{ $order->order_number }}</h6>
                                                <p class="text-xs text-secondary mb-0">
                                                    {{ $order->outlet->name ?? 'Outlet' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0 text-dark">{{ $order->customer_name ?? 'Guest' }}</p>
                                        <p class="text-xs text-secondary mb-0">
                                            {{ $order->table ? 'Meja ' . $order->table->table_number : 'Takeaway' }}
                                        </p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @php
                                            $badgeClass = match($order->payment_status) {
                                                'paid' => 'success',
                                                'partial' => 'warning',
                                                'refunded' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge badge-sm bg-gradient-{{ $badgeClass }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">
                                            Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">
                                            {{ $order->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button class="btn btn-link text-secondary mb-0 px-2" 
                                                wire:click="showReceipt('{{ $order->id }}')" 
                                                title="Lihat Detail / Struk">
                                            <i class="material-icons text-xs">visibility</i> Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-secondary">
                                        <i class="material-icons opacity-3" style="font-size: 48px;">receipt_long</i>
                                        <p class="mt-2 mb-0">Tidak ada transaksi ditemukan pada periode ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                <div class="px-3 mt-3">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>

        {{-- 4. MODAL STRUK (RECEIPT & REFUND) --}}
        @if($showReceiptModal && $selectedOrder)
        <div class="fixed-top w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="bg-white shadow-lg rounded-3" style="width: 380px; max-height: 90vh; overflow-y: auto;">
                
                {{-- Header Modal --}}
                <div class="p-3 bg-gradient-dark text-white d-flex justify-content-between align-items-center rounded-top">
                    <h6 class="mb-0 text-white">Detail Transaksi</h6>
                    <button wire:click="closeReceipt" class="btn btn-link text-white p-0 mb-0"><i class="material-icons">close</i></button>
                </div>

                {{-- Receipt Content --}}
                <div class="p-4" style="font-family: 'Courier New', monospace;">
                    <div class="text-center mb-3">
                        <h5 class="fw-bold mb-0">{{ $selectedOrder->outlet->name ?? 'NIBBLE POS' }}</h5>
                        <p class="text-xs text-muted mb-0">Receipt #{{ $selectedOrder->order_number }}</p>
                        <p class="text-xs text-muted">{{ $selectedOrder->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    
                    <hr class="border-dashed">
                    
                    {{-- Item List --}}
                    <div class="py-2">
                        @foreach($selectedOrder->items as $item)
                        <div class="d-flex justify-content-between text-xs mb-1">
                            <span>
                                {{ $item->quantity }}x {{ $item->product->name ?? 'Item' }}
                                @if(isset($item->cost_price) && auth()->user()->role->slug === 'super_admin') 
                                    {{-- Debug: Tampilkan HPP hanya untuk Super Admin jika perlu --}}
                                    {{-- <span class="text-danger" style="font-size: 8px;">(HPP: {{ $item->cost_price }})</span> --}}
                                @endif
                            </span>
                            <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>

                    <hr class="border-dashed">
                    
                    {{-- Totals --}}
                    <div class="d-flex justify-content-between text-sm fw-bold mb-1">
                        <span>TOTAL</span>
                        <span>Rp {{ number_format($selectedOrder->grand_total, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between text-xs text-muted">
                        <span>Status</span>
                        <span class="text-uppercase fw-bold text-{{ $selectedOrder->payment_status == 'paid' ? 'success' : 'danger' }}">
                            {{ $selectedOrder->payment_status }}
                        </span>
                    </div>

                    {{-- Info Refund (Jika ada) --}}
                    @if($selectedOrder->payment_status === 'refunded')
                        <div class="alert alert-danger text-white text-xs mt-3 p-2 text-center mb-0">
                            Transaksi ini telah di-refund.
                        </div>
                    @endif
                </div>

                {{-- Actions Footer --}}
                <div class="p-3 bg-gray-100 border-top text-center rounded-bottom">
                    <button class="btn btn-dark w-100 mb-2" onclick="window.print()">
                        <i class="material-icons text-sm me-1">print</i> Cetak Struk
                    </button>
                    
                    @if(in_array($selectedOrder->payment_status, ['paid', 'partial']))
                        @if(auth()->user()->role->slug === 'admin' || auth()->user()->role->slug === 'super_admin')
                            <button class="btn btn-outline-danger w-100 mb-0" 
                                    onclick="confirm('Yakin refund transaksi ini? Stok akan dikembalikan dan uang dikurangi dari laporan.') || event.stopImmediatePropagation()"
                                    wire:click="processRefund('{{ $selectedOrder->id }}')">
                                <i class="material-icons text-sm me-1">undo</i> Refund / Batalkan
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- Custom CSS --}}
    <style>
        .border-dashed { border-top: 1px dashed #ccc; margin: 10px 0; }
        .bg-gray-100 { background-color: #f8f9fa !important; }
    </style>
</div>