<div class="bg-light min-vh-100 pb-7">
    <style>
        .category-scroll::-webkit-scrollbar { display: none; }
        .product-card { transition: transform 0.2s; border: none; border-radius: 15px; }
        .product-card:active { transform: scale(0.95); }
        .floating-cart-btn { position: fixed; bottom: 20px; right: 20px; z-index: 1000; border-radius: 50px; padding: 15px 25px; }
        .sold-out { filter: grayscale(1); opacity: 0.6; }
    </style>

    <div class="bg-gradient-dark pt-5 pb-4 px-4 shadow-primary border-radius-bottom-end">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="text-white font-weight-bolder mb-0">Artisan Bakery</h3>
                <p class="text-white opacity-8 text-sm">Table {{ $tableId ?? 'Counter' }}</p>
            </div>
            <div class="bg-white border-radius-lg p-2 shadow-sm">
                <i class="material-icons text-dark">qr_code_scanner</i>
            </div>
        </div>
    </div>

    <div class="sticky-top bg-light py-3 shadow-sm px-3 mb-4" style="top: 0; z-index: 99;">
        <div class="d-flex overflow-auto category-scroll gap-2">
            @foreach($categories as $cat)
                <button wire:click="$set('category', '{{ $cat }}')" 
                    class="btn btn-sm rounded-pill mb-0 text-nowrap {{ $category == $cat ? 'btn-primary' : 'btn-outline-primary shadow-none' }}">
                    {{ $cat }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="container-fluid">
        <div class="row px-2">
            @forelse($products as $product)
            <div class="col-6 col-md-4 col-lg-3 mb-4">
                <div class="card product-card shadow-lg h-100">
                    <div class="position-relative">
                        <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('assets/img/products/product-details-1.jpg') }}" 
                             class="card-img-top border-radius-lg" style="height: 160px; object-fit: cover;">
                    </div>
                    <div class="card-body p-3 d-flex flex-column">
                        <h6 class="text-dark font-weight-bold mb-1 text-truncate">{{ $product->name }}</h6>
                        <p class="text-xs text-secondary mb-2">{{ Str::limit($product->description, 30) }}</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="text-primary font-weight-black">${{ number_format($product->price, 2) }}</span>
                            <button wire:click="addToCart({{ $product->id }})" class="btn btn-icon-only btn-rounded btn-outline-primary mb-0">
                                <i class="material-icons text-sm">add</i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <img src="{{ asset('assets/img/illustrations/error-404.png') }}" class="w-30 mb-3">
                <p class="text-secondary">No items available in this category.</p>
            </div>
            @endforelse
        </div>
    </div>

    @if(count($cart) > 0)
    <button wire:click="$set('showCart', true)" class="btn btn-primary floating-cart-btn shadow-lg animate__animated animate__bounceIn">
        <div class="d-flex align-items-center">
            <i class="material-icons me-2">shopping_basket</i>
            <span>{{ count($cart) }} Items • ${{ number_format($this->total, 2) }}</span>
        </div>
    </button>
    @endif

    @if($showCart)
    <div class="position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.5); z-index: 1050;" wire:click="$set('showCart', false)"></div>
    <div class="position-fixed bottom-0 start-0 w-100 bg-white border-radius-top-start shadow-lg animate__animated animate__slideInUp" style="z-index: 1100; max-height: 80vh;">
        <div class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="font-weight-black mb-0">My Basket</h5>
                <i class="material-icons text-secondary cursor-pointer" wire:click="$set('showCart', false)">expand_more</i>
            </div>
            
            <div class="overflow-auto" style="max-height: 40vh;">
                @foreach($cart as $id => $item)
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ $item['image'] ? asset('storage/'.$item['image']) : asset('assets/img/products/product-1-min.jpg') }}" class="avatar avatar-md border-radius-lg me-3">
                    <div class="flex-grow-1">
                        <h6 class="text-sm font-weight-bold mb-0">{{ $item['name'] }}</h6>
                        <p class="text-xs text-secondary mb-0">${{ number_format($item['price'], 2) }}</p>
                    </div>
                    <div class="d-flex align-items-center bg-light rounded-pill px-2">
                        <button class="btn btn-link text-dark px-2 mb-0" wire:click="updateQty({{ $id }}, -1)">-</button>
                        <span class="text-sm font-weight-bold mx-2">{{ $item['qty'] }}</span>
                        <button class="btn btn-link text-dark px-2 mb-0" wire:click="updateQty({{ $id }}, 1)">+</button>
                    </div>
                </div>
                @endforeach
            </div>

            <hr class="horizontal dark">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="text-secondary">Grand Total</span>
                <span class="h4 font-weight-black mb-0">${{ number_format($this->total, 2) }}</span>
            </div>
            <button wire:click="checkout" class="btn btn-primary w-100 btn-lg border-radius-lg py-3 shadow-primary">
                PLACE ORDER NOW
            </button>
        </div>
    </div>
    @endif

    @if($orderPlaced)
    <div class="position-fixed top-50 start-50 translate-middle bg-white p-5 border-radius-xl shadow-lg text-center" style="z-index: 2000; width: 85%;">
        <div class="bg-success-soft border-radius-circle d-inline-block p-3 mb-4">
            <i class="material-icons text-success text-5xl">check_circle</i>
        </div>
        <h4 class="font-weight-black">Order Received!</h4>
        <p class="text-secondary">Your food is being prepared. Please stay at Table {{ $tableId }}.</p>
        <button wire:click="$set('orderPlaced', false)" class="btn btn-dark w-100 mt-3">OK</button>
    </div>
    @endif
</div>