<div class="container-fluid py-4">
    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Refund</p>
                                <h5 class="font-weight-bolder mb-0">{{ $stats['total_refunds_count'] }}</h5>
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
        
        <div class="col-xl-3 col-sm-6 mb-3">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Amount</p>
                                <h5 class="font-weight-bolder mb-0 text-danger">
                                    Rp {{ number_format($stats['total_refunded_amount'], 0, ',', '.') }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                <i class="material-icons opacity-10">payments</i>
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
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Pending</p>
                                <h5 class="font-weight-bolder mb-0 text-warning">{{ $stats['pending_refunds'] }}</h5>
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
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Approved</p>
                                <h5 class="font-weight-bolder mb-0 text-success">{{ $stats['approved_refunds'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="material-icons opacity-10">check_circle</i>
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
                    <input type="text" class="form-control form-control-sm" placeholder="Cari order..." wire:model.live="search">
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
                    <select class="form-control form-control-sm" wire:model.live="filterStatus">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="completed">Completed</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control form-control-sm" wire:model.live="filterType">
                        <option value="all">All Types</option>
                        <option value="full">Full Refund</option>
                        <option value="partial">Partial Refund</option>
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

    {{-- Refunds Table --}}
    <div class="card">
        <div class="card-header pb-0">
            <h6>Refund History</h6>
        </div>
        <div class="card-body px-0 pb-2">
            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Order</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Amount</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Reason</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Requested By</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($refunds as $refund)
                            <tr>
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">{{ $refund->order->order_number }}</h6>
                                            <p class="text-xs text-secondary mb-0">{{ $refund->order->customer_name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-sm bg-{{ $refund->refund_type === 'full' ? 'danger' : 'warning' }}">
                                        {{ ucfirst($refund->refund_type) }}
                                    </span>
                                </td>
                                <td>
                                    <p class="text-sm font-weight-bold mb-0">
                                        Rp {{ number_format($refund->total_refund_amount, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-secondary mb-0">
                                        of Rp {{ number_format($refund->order->grand_total, 0, ',', '.') }}
                                    </p>
                                </td>
                                <td>
                                    <p class="text-xs mb-0">{{ ucfirst(str_replace('_', ' ', $refund->reason)) }}</p>
                                    @if($refund->notes)
                                        <small class="text-muted">{{ Str::limit($refund->notes, 30) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <p class="text-xs mb-0">{{ $refund->requestedBy->name ?? '-' }}</p>
                                    <p class="text-xs text-secondary mb-0">{{ $refund->requested_at?->format('d M H:i') }}</p>
                                </td>
                                <td>
                                    <span class="badge badge-sm bg-gradient-{{ 
                                        $refund->status === 'completed' ? 'success' : 
                                        ($refund->status === 'pending' ? 'warning' : 
                                        ($refund->status === 'rejected' ? 'danger' : 'info')) 
                                    }}">
                                        {{ ucfirst($refund->status) }}
                                    </span>
                                    @if($refund->approved_by)
                                        <p class="text-xs text-secondary mb-0">
                                            by {{ $refund->approvedBy->name ?? '-' }}
                                        </p>
                                    @endif
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $refund->created_at->format('d M Y') }}</p>
                                    <p class="text-xs text-secondary mb-0">{{ $refund->created_at->format('H:i') }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="material-icons text-secondary" style="font-size: 48px;">undo</i>
                                    <p class="text-secondary">No refunds found</p>
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
        {{ $refunds->links() }}
    </div>
</div>