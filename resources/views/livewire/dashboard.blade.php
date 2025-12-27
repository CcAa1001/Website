<div class="container-fluid py-4">
    <!-- Quick Insights -->
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card shadow-primary border-radius-xl">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">payments</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize font-weight-bold">Daily Revenue</p>
                        <h4 class="mb-0">${{ number_format($totalSales, 2) }}</h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0"><span class="text-success text-sm font-weight-bolder">+15% </span>vs yesterday</p>
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
                    <p class="mb-0 text-sm">Updated just now</p>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card mt-4">
        <div class="card-header pb-0">
            <h6>Generate Table QR Links</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($tables as $t)
                <div class="col-md-3 mb-3">
                    <div class="p-3 border border-radius-md text-center">
                        <p class="font-weight-bold mb-1">Table {{ $t }}</p>
                        @php
                            // Construct the secure URL
                            $url = url("/order/{$t}?key=" . env('BAKERY_ORDER_KEY', 'bakery123'));
                        @endphp
                        <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl={{ urlencode($url) }}" class="img-fluid">
                        <br>
                        <a href="{{ $url }}" target="_blank" class="text-xs">Open Link</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <!-- Table Management & QR Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 px-3">
                        <h6 class="text-white text-capitalize ps-3 font-weight-bold">QR Table Link Generator</h6>
                    </div>
                </div>
                <div class="card-body px-4 pb-2">
                    <div class="row">
                        @foreach($tables as $t)
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="p-3 border border-radius-lg bg-gray-100 text-center shadow-sm">
                                <div class="bg-white p-2 border-radius-md shadow-sm d-inline-block mb-2">
                                    <i class="material-icons text-4xl text-dark">qr_code_scanner</i>
                                </div>
                                <h6 class="mb-0 font-weight-black">Table {{ $t }}</h6>
                                <p class="text-xs text-secondary mb-3">Link for physical QR</p>
                                <a href="{{ url('/order/' . $t) }}" target="_blank" class="btn btn-sm bg-gradient-dark w-100 mb-0">Open Ordering Page</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Log -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0 p-3">
                    <h6 class="mb-0">Recent Activity</h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Table Source</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total Amount</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td><div class="ps-3 text-sm font-weight-bold">Table {{ $order->table_number }}</div></td>
                                    <td><span class="text-xs font-weight-bold">${{ number_format($order->total_amount, 2) }}</span></td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm bg-gradient-success">Preparing</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">{{ $order->created_at->diffForHumans() }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>