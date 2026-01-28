<div class="container-fluid py-4">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show text-white" role="alert">
            <span class="alert-icon"><i class="material-icons text-sm">check_circle</i></span>
            <span class="alert-text">{{ session('message') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">receipt_long</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Total Transaksi</p>
                        <h4 class="mb-0">{{ $stats['total_transactions'] }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0"><span class="text-success text-sm font-weight-bolder">Update</span> baru saja</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">payments</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Pendapatan Bersih</p>
                        <h4 class="mb-0">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0"><span class="text-xs text-muted">Setelah dikurangi refund</span></p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">account_balance_wallet</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Kas Tunai (Modal)</p>
                        <h4 class="mb-0">Rp {{ number_format($stats['modal'], 0, ',', '.') }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0"><span class="text-xs text-muted">Dari pembayaran Cash</span></p>
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
                        <p class="text-sm mb-0 text-capitalize">Total Refund</p>
                        <h4 class="mb-0">Rp {{ number_format($stats['total_refunded'], 0, ',', '.') }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0"><span class="text-danger text-sm font-weight-bolder">Alert</span> transaksi batal</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="card my-4">
        <div class="card-body px-3 pb-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-outline input-group-sm">
                        <label class="form-label">Cari Order ID / Nama...</label>
                        <input type="text" class="form-control" wire:model.live.debounce.500ms="search">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group input-group-static input-group-sm">
                        <select class="form-control" wire:model.live="filterDateRange">
                            <option value="today">Hari Ini</option>
                            <option value="yesterday">Kemarin</option>
                            <option value="week">7 Hari Terakhir</option>
                            <option value="month">30 Hari Terakhir</option>
                            <option value="custom">Pilih Tanggal</option>
                        </select>
                    </div>
                </div>
                
                @if($filterDateRange === 'custom')
                <div class="col-md-2">
                    <div class="input-group input-group-static input-group-sm">
                        <input type="date" class="form-control" wire:model.live="filterStartDate">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group input-group-static input-group-sm">
                        <input type="date" class="form-control" wire:model.live="filterEndDate">
                    </div>
                </div>
                @endif

                <div class="col-md-2">
                    <div class="input-group input-group-static input-group-sm">
                        <select class="form-control" wire:model.live="filterPaymentStatus">
                            <option value="all">Semua Status Bayar</option>
                            <option value="paid">Lunas (Paid)</option>
                            <option value="partial">Sebagian (Partial)</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-1 text-end">
                    <button wire:click="clearFilters" class="btn btn-icon btn-sm btn-outline-secondary mb-0" title="Reset Filter">
                        <i class="material-icons text-sm">refresh</i>
                    </button>
                    <button wire:click="exportCSV" class="btn btn-icon btn-sm btn-success mb-0 ms-2" title="Download CSV">
                        <i class="material-icons text-sm">download</i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="card">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize ps-3">Riwayat Transaksi Detail</h6>
            </div>
        </div>
        <div class="card-body px-0 pb-2">
            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Order Info</th>
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
                            <tr>
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">{{ $order->order_number }}</h6>
                                            <p class="text-xs text-secondary mb-0">{{ $order->customer_name ?? 'Guest' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $order->created_at->format('d M Y') }}</p>
                                    <p class="text-xs text-secondary mb-0">{{ $order->created_at->format('H:i') }}</p>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">{{ $order->table->name ?? ($order->table->table_number ?? 'TA') }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    @if($totalRefunded > 0)
                                        <span class="text-secondary text-xs" style="text-decoration: line-through;">
                                            Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                                        </span>
                                        <br>
                                        <span class="text-success text-sm font-weight-bold">
                                            Rp {{ number_format($order->grand_total - $totalRefunded, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-secondary text-sm font-weight-bold">
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
                                            class="btn btn-link text-secondary mb-0"
                                            title="Lihat Item">
                                        <i class="material-icons">{{ $expandedOrderId === $order->id ? 'expand_less' : 'visibility' }}</i>
                                    </button>
                                </td>
                            </tr>
                            
                            {{-- Expandable Items Row --}}
                            @if($expandedOrderId === $order->id)
                                <tr>
                                    <td colspan="6" class="p-0">
                                        <div class="bg-gray-100 p-4">
                                            <h6 class="text-xs font-weight-bold mb-2 text-uppercase text-secondary">Detail Pesanan</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0 bg-white border-radius-md" style="border-collapse: separate; border-spacing: 0;">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th class="text-xs font-weight-bolder ps-3 py-2">Item</th>
                                                            <th class="text-xs font-weight-bolder text-center py-2">Qty</th>
                                                            <th class="text-xs font-weight-bolder text-end py-2">Harga</th>
                                                            <th class="text-xs font-weight-bolder text-end pe-3 py-2">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($order->items as $item)
                                                            @php
                                                                $refundInfo = $this->isItemRefunded($order, $item->id);
                                                                $isRefunded = $refundInfo['refunded'];
                                                                $refundedQty = $refundInfo['quantity'];
                                                            @endphp
                                                            <tr>
                                                                <td class="ps-3 border-bottom">
                                                                    <div class="d-flex flex-column">
                                                                        <span class="text-sm {{ $isRefunded ? 'text-decoration-line-through text-danger' : '' }}">{{ $item->product_name }}</span>
                                                                        @if($item->variant_name)
                                                                            <span class="text-xs text-muted">{{ $item->variant_name }}</span>
                                                                        @endif
                                                                        @if($isRefunded)
                                                                            <span class="badge bg-danger mt-1" style="width: fit-content;">Refunded: {{ $refundedQty }}</span>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                                <td class="text-center text-sm border-bottom">{{ $item->quantity }}</td>
                                                                <td class="text-end text-sm border-bottom">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                                <td class="text-end text-sm border-bottom pe-3 font-weight-bold">
                                                                    Rp {{ number_format($item->total_price, 0, ',', '.') }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        
                                                        {{-- Footer Totals --}}
                                                        <tr>
                                                            <td colspan="3" class="text-end text-xs font-weight-bold pt-3">Subtotal:</td>
                                                            <td class="text-end text-xs font-weight-bold pt-3 pe-3">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                                        </tr>
                                                        @if($totalRefunded > 0)
                                                        <tr>
                                                            <td colspan="3" class="text-end text-xs font-weight-bold text-danger">Refunded:</td>
                                                            <td class="text-end text-xs font-weight-bold text-danger pe-3">-Rp {{ number_format($totalRefunded, 0, ',', '.') }}</td>
                                                        </tr>
                                                        @endif
                                                        <tr>
                                                            <td colspan="3" class="text-end text-sm font-weight-bolder pb-3">Total Akhir:</td>
                                                            <td class="text-end text-sm font-weight-bolder text-success pb-3 pe-3">Rp {{ number_format($order->grand_total - $totalRefunded, 0, ',', '.') }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            {{-- Payment Info --}}
                                            <div class="mt-3">
                                                <h6 class="text-xs font-weight-bold mb-1 text-uppercase text-secondary">Riwayat Pembayaran</h6>
                                                @foreach($order->payments as $payment)
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge badge-dot me-4">
                                                            <i class="bg-success"></i>
                                                            <span class="text-dark text-xs">{{ $payment->created_at->format('H:i') }} - {{ $payment->paymentMethod->name ?? 'Unknown' }}</span>
                                                        </span>
                                                        <span class="text-dark text-xs font-weight-bold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <p class="text-sm text-secondary mb-0">Tidak ada transaksi ditemukan.</p>
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
</div>