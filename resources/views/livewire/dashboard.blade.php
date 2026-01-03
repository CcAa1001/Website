<div class="container-fluid py-4">
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card shadow-primary border-radius-xl">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">payments</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize font-weight-bold">Daily Revenue</p>
                        <h4 class="mb-0">Rp {{ number_format($todaysEarnings, 0, ',', '.') }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0"><span class="text-success text-sm font-weight-bolder">Update </span> today</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card shadow-info border-radius-xl">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">list_alt</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize font-weight-bold">Pending Orders</p>
                        <h4 class="mb-0">{{ $activeOrders }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0 text-sm">Needs attention</p>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card shadow-success border-radius-xl">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">receipt_long</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize font-weight-bold">Today's Orders</p>
                        <h4 class="mb-0">{{ $totalOrders }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0 text-sm">Total transactions</p>
                </div>
            </div>
        </div>

         <div class="col-xl-3 col-sm-6">
            <div class="card shadow-warning border-radius-xl">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-warning shadow-warning text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">person_add</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize font-weight-bold">New Customers</p>
                        <h4 class="mb-0">{{ $newCustomers }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0 text-sm">This month</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 px-3">
                        <h6 class="text-white text-capitalize ps-3 font-weight-bold">QR Table Link Generator</h6>
                    </div>
                </div>
                <div class="card-body px-4 pb-2">
                    <div class="row">
                        @forelse($tables as $t)
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="p-3 border border-radius-lg bg-gray-100 text-center shadow-sm">
                                <div class="bg-white p-2 border-radius-md shadow-sm d-inline-block mb-2">
                                    @php
                                        $url = url("/order/{$t}?key=" . env('BAKERY_ORDER_KEY', 'bakery123'));
                                    @endphp
                                    <img src="https://chart.googleapis.com/chart?chs=100x100&cht=qr&chl={{ urlencode($url) }}" class="img-fluid" alt="QR Code">
                                </div>
                                <h6 class="mb-0 font-weight-black">Table {{ $t }}</h6>
                                <p class="text-xs text-secondary mb-3">Scan to Order</p>
                                <a href="{{ $url }}" target="_blank" class="btn btn-sm bg-gradient-dark w-100 mb-0">Open Link</a>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-4">
                            <p class="text-secondary">No tables found. Please add tables in the database.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0 p-3">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-0">Recent Activity</h6>
                        <a href="#" class="text-secondary text-xs font-weight-bold">View All</a>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Order Info</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total Amount</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">
                                                    {{ $order->table ? 'Table ' . $order->table->table_number : ($order->order_type == 'takeaway' ? 'Take Away' : 'Unknown') }}
                                                </h6>
                                                <p class="text-xs text-secondary mb-0">{{ $order->order_number }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <span class="text-xs font-weight-bold">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                                    </td>
                                    
                                    <td class="align-middle text-center text-sm">
                                        @php
                                            $badgeClass = match($order->status) {
                                                'completed', 'paid', 'served' => 'bg-gradient-success',
                                                'pending', 'confirmed' => 'bg-gradient-warning',
                                                'preparing', 'ready' => 'bg-gradient-info',
                                                'cancelled' => 'bg-gradient-danger',
                                                default => 'bg-gradient-secondary',
                                            };
                                        @endphp
                                        <span class="badge badge-sm {{ $badgeClass }}">{{ ucfirst($order->status) }}</span>
                                    </td>
                                    
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">{{ $order->created_at->diffForHumans() }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <span class="text-sm text-secondary">No recent orders found.</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>