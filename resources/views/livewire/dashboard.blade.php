<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <h3 class="text-white text-capitalize ps-3">Morning Bakery Outlet</h3>
                <p class="text-white ps-3 opacity-8">Welcome back! Here is what's happening at your bakery today.</p>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">weekend</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Today's Sales</p>
                        <h4 class="mb-0">${{ number_format($todaySales, 2) }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0"><span class="text-success text-sm font-weight-bolder">+5% </span>than yesterday</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-warning shadow-warning text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">inventory</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Low Stock Alerts</p>
                        <h4 class="mb-0">{{ $lowStockCount }} Items</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0">Need to restock soon</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6 col-7">
                            <h6>Recent Transactions</h6>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Order ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Method</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td><span class="text-xs font-weight-bold"> #{{ $order->id }} </span></td>
                                    <td><span class="text-xs font-weight-bold"> ${{ number_format($order->total_amount, 2) }} </span></td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm bg-gradient-success"> {{ $order->payment_method }} </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">{{ $order->created_at->format('d M, H:i') }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6>Quick Actions</h6>
                </div>
                <div class="card-body p-3">
                    <a href="{{ route('pos') }}" class="btn bg-gradient-success w-100 mb-2">Buka Kasir (POS)</a>
                    <a href="{{ route('inventory') }}" class="btn bg-gradient-info w-100 mb-2">Update Stok Roti</a>
                    <button class="btn bg-gradient-dark w-100 mb-2" onclick="window.print()">Cetak Laporan Harian</button>
                    
                    <div class="mt-4 text-center">
                        <h6>QR Menu Pelanggan</h6>
                        <div class="border p-2 d-inline-block">
                             {!! QrCode::size(150)->generate(route('public.menu', ['userId' => auth()->id()])) !!}
                        </div>
                        <p class="text-xs mt-2">Pajang QR ini di meja kasir</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>