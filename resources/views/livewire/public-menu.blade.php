<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="font-weight-bold">{{ $vendor->name }}'s Menu</h2>
        <p class="text-muted">Order your favorites below</p>
    </div>
    <div class="row">
        @foreach($products as $p)
        <div class="col-md-4 mb-4">
            <div class="card shadow-lg border-0 border-radius-xl">
                <div class="card-body p-3 text-center">
                    <h5>{{ $p->name }}</h5>
                    <h4 class="text-primary">${{ number_format($p->price, 2) }}</h4>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>