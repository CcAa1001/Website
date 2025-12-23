<div class="col-xl-12 mb-4">
    <div class="card bg-gradient-primary">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-8">
                    <div class="numbers">
                        <p class="text-white text-sm mb-0 text-capitalize font-weight-bold opacity-7">Public Ordering Platform</p>
                        <h5 class="text-white font-weight-bolder mb-0">Share your Digital Menu</h5>
                        <a href="{{ route('public.menu', ['userId' => auth()->id()]) }}" target="_blank" class="btn btn-white btn-sm mb-0 mt-3">View Digital Menu</a>
                    </div>
                </div>
                <div class="col-4 text-end">
                    <div class="icon icon-shape bg-white shadow text-center border-radius-md">
                        <i class="material-icons text-dark opacity-10" aria-hidden="true">qr_code</i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>