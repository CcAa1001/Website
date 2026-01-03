<div class="container py-4">
    @if($orderSuccess)
    <div class="card text-center mt-5 shadow border-radius-xl py-5">
        <div class="card-body">
            <h1 class="text-success mb-3">✅</h1>
            <h3 class="font-weight-bold">Order Received!</h3>
            <p>Order Number: <strong class="text-primary">{{ $orderNumberStr }}</strong></p>
            <p class="text-muted">Please wait while we prepare your food.</p>
            <button wire:click="$set('orderSuccess', false)" class="btn theme-btn bg-gradient-primary mt-3">Order Again</button>
        </div>
    </div>
    @else
    <div class="d-flex justify-content-between align-items-center mb-4 sticky-top bg-white py-3 shadow-sm px-3 border-radius-lg" style="z-index: 1000;">
        <div>
            <h5 class="font-weight-bold mb-0">Digital Menu</h5>
            <span class="badge bg-gradient-info">Table {{ $tableNumber }}</span>
        </div>
        <button class="btn btn-dark position-relative mb-0" data-bs-toggle="modal" data-bs-target="#cartModal">
            <i class="material-icons text-lg">shopping_cart</i>
            @if(count($cart) > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                {{ count($cart) }}
            </span>
            @endif
        </button>
    </div>

    <div class="row">
        @forelse($products as $product)
        <div class="col-6 col-md-4 mb-4">
            <div class="card h-100 shadow-sm border-0 border-radius-lg overflow-hidden">
                @if($product->image_url)
                    <img src="{{ asset('storage/' . $product->image_url) }}" 
                         class="card-img-top" 
                         style="height: 140px; object-fit: cover;"
                         onerror="this.src='https://via.placeholder.com/300x200?text=Menu+Image'">
                @else
                    <div class="card-img-top d-flex align-items-center justify-content-center bg-gray-200" style="height: 140px;">
                        <i class="material-icons text-secondary opacity-5" style="font-size: 48px;">restaurant_menu</i>
                    </div>
                @endif

                <div class="card-body p-3">
                    <h6 class="text-dark font-weight-bold mb-1 text-sm">{{ $product->name }}</h6>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="text-xs font-weight-bold text-primary">Rp{{ number_format($product->base_price, 0, ',', '.') }}</span>
                        <button wire:click="addToCart('{{ $product->id }}')" class="btn btn-icon btn-sm theme-btn bg-gradient-primary mb-0">
                            <i class="material-icons">add</i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p class="text-secondary">No items available.</p>
        </div>
        @endforelse
    </div>
    @endif
    </div>