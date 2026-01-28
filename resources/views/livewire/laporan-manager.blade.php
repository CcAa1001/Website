<div class="container-fluid py-4">
    <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="theme-card-header bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 px-3 d-flex justify-content-between align-items-center">
                <h6 class="text-white ps-3 mb-0">Laporan Penjualan</h6>
                <button wire:click="exportCSV" class="btn btn-sm btn-white mb-0 me-3">Export CSV</button>
            </div>
        </div>
        <div class="card-body px-4">
            <div class="row mb-4 mt-3">
                <div class="col-md-3">
                    <label class="text-xs font-weight-bold">Dari</label>
                    <input type="date" class="form-control border px-2" wire:model.live="dateFrom">
                </div>
                <div class="col-md-3">
                    <label class="text-xs font-weight-bold">Sampai</label>
                    <input type="date" class="form-control border px-2" wire:model.live="dateTo">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr><th class="text-xxs font-weight-bolder opacity-7 ps-3">Order #</th><th>Total</th><th>Status</th><th>Tanggal</th></tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $o)
                        <tr>
                            <td><p class="text-sm font-weight-bold mb-0 ps-3">{{ $o->order_number }}</p></td>
                            <td><p class="text-sm mb-0">Rp{{ number_format($o->grand_total, 0, ',', '.') }}</p></td>
                            <td><span class="badge badge-sm bg-gradient-success">{{ $o->status }}</span></td>
                            <td class="text-xs font-weight-bold">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</div>