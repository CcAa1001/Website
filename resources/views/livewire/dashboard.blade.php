<div class="container-fluid py-4">
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card h-100">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">payments</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Harian</p>
                        <h4 class="mb-0">Rp {{ number_format($todaysEarnings, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        </div>

    <div class="row mt-4">
        <div class="col-lg-8 mb-4">
            <div class="card z-index-2">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
                    <div class="theme-card-header bg-gradient-primary shadow-primary border-radius-lg py-3">
                        <canvas id="salesChart" height="170"></canvas>
                    </div>
                </div>
                <div class="card-body">
                    <h6>Tren Penjualan Mingguan</h6>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card z-index-2">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
                    <div class="theme-card-header bg-gradient-info shadow-info border-radius-lg py-3">
                        <canvas id="categoryChart" height="170"></canvas>
                    </div>
                </div>
                <div class="card-body">
                    <h6>Kategori Terlaris</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="theme-card-header bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 px-3">
                        <h6 class="text-white ps-3">Generator QR Meja</h6>
                    </div>
                </div>
                <div class="card-body px-4 pt-4">
                    <div class="row">
                        @foreach($tables as $t)
                        <div class="col-md-3 col-6 mb-4 text-center">
                            <div class="p-3 border-radius-lg bg-gray-100 shadow-sm">
                                <div class="bg-white p-2 d-inline-block mb-2 border rounded">
                                    <div class="qr-code-canvas" data-url="{{ route('public.menu', $t) }}"></div>
                                </div>
                                <h6 class="mb-0">Meja {{ $t }}</h6>
                                <a href="{{ route('public.menu', $t) }}" target="_blank" class="btn btn-sm theme-btn bg-gradient-primary w-100 mt-2">Buka Menu</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    function renderDashboardCharts() {
        const salesLabels = @json($salesData['labels']);
        const salesValues = @json($salesData['values']);
        
        const salesCtx = document.getElementById("salesChart")?.getContext("2d");
        if(salesCtx) {
            new Chart(salesCtx, {
                type: "line",
                data: {
                    labels: salesLabels,
                    datasets: [{ label: "Sales", borderColor: "#fff", data: salesValues, tension: 0.4, fill: true, backgroundColor: 'rgba(255,255,255,0.1)' }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { ticks: { color: "#fff" } }, x: { ticks: { color: "#fff" } } } }
            });
        }
        
        const catCtx = document.getElementById("categoryChart")?.getContext("2d");
        if(catCtx) {
            new Chart(catCtx, {
                type: "doughnut",
                data: {
                    labels: @json($categoryData['labels']),
                    datasets: [{ backgroundColor: ['#fff', '#e91e63', '#344767', '#fb8c00', '#43A047'], data: @json($categoryData['values']) }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }
    }
</script>
@endpush