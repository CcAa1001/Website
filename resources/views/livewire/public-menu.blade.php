<div class="bg-gray-100 min-vh-100 pb-6">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .category-pill { white-space: nowrap; transition: 0.3s; cursor: pointer; border: none; }
        .glass-nav { background: rgba(255, 255, 255, 0.8) !important; backdrop-filter: blur(10px); }
        .product-img { height: 140px; object-fit: cover; border-radius: 12px; }
        .fab-cart { position: fixed; bottom: 20px; right: 20px; z-index: 1000; border-radius: 50px; padding: 15px 25px; font-weight: 800; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .search-input { border-radius: 50px !important; padding-left: 20px !important; background: white !important; }
    </style>

    <div class="bg-gradient-primary pt-5 pb-4 px-4 shadow-lg border-radius-bottom-end-lg">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-white font-weight-bolder mb-0">Artisan Bakery</h2>
            <span class="badge bg-white text-primary rounded-pill px-3">Meja {{ $tableId ?? 'Counter' }}</span>
        </div>
        <div class="input-group input-group-outline bg-white rounded-pill shadow-sm">
            <input type="text" wire:model.debounce.300ms="search" class="form-control search-input" placeholder="Cari roti favoritmu...">
        </div>
    </div>

    <div class="sticky-top glass-nav py-3 px-3 shadow-sm" style="top: 0; z-index: 99;">
        <div class="d-flex overflow-auto gap-2 pb-1" style="scrollbar-width: none;">
            @foreach($categories as $cat)
                <button wire:click="$set('category', '{{ $cat }}')" 
                    class="btn btn-sm rounded-pill {{ $category == $cat ? 'btn-primary' : 'btn-outline-primary shadow-none' }}">
                    {{ $cat }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="container-fluid mt-4">
        <div class="row px-2">
            @forelse($products as $product)
            <div class="col-6 col-md-4 col-lg-2 mb-4 animate__animated animate__fadeIn">
                <div class="card border-0 shadow-sm border-radius-xl overflow-hidden h-100">
                    <img src="{{ $product->image ? asset('storage/'.$product->image) : asset('assets/img/products/product-details-1.jpg') }}" class="product-img w-100">
                    <div class="card-body p-2 d-flex flex-column">
                        <h6 class="text-xs font-weight-bold mb-1 text-truncate">{{ $product->name }}</h6>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <span class="text-sm font-weight-black text-primary">${{ number_format($product->price, 2) }}</span>
                            <button wire:click="addToCart({{ $product->id }})" class="btn btn-primary btn-sm btn-icon-only rounded-circle mb-0 shadow-none">
                                <i class="material-icons text-xs">add</i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <p class="text-secondary">Produk tidak ditemukan.</p>
            </div>
            @endforelse
        </div>
    </div>

    @if(count($cart) > 0)
    <button wire:click="$set('showCart', true)" class="btn btn-dark fab-cart animate__animated animate__bounceIn">
        <i class="material-icons me-2">shopping_bag</i>
        {{ count($cart) }} Item • ${{ number_format($this->total, 2) }}
    </button>
    @endif

    @if($showCart)
    <div class="position-fixed top-0 start-0 w-100 h-100" style="background:rgba(0,0,0,0.4); z-index:1050;" wire:click="$set('showCart', false)"></div>
    <div class="position-fixed bottom-0 start-0 w-100 bg-white border-radius-top-start-lg animate__animated animate__slideInUp" style="z-index:1100; max-height:80vh;">
        <div class="p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h5 class="font-weight-black mb-0">Pesanan Kamu</h5>
                <i class="material-icons text-secondary cursor-pointer" wire:click="$set('showCart', false)">close</i>
            </div>
            <div class="overflow-auto" style="max-height:40vh;">
                @foreach($cart as $id => $item)
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ $item['image'] ? asset('storage/'.$item['image']) : asset('assets/img/products/product-1-min.jpg') }}" class="avatar avatar-md border-radius-lg me-3">
                    <div class="flex-grow-1">
                        <h6 class="text-sm font-weight-bold mb-0">{{ $item['name'] }}</h6>
                        <p class="text-xs text-secondary mb-0">${{ number_format($item['price'], 2) }}</p>
                    </div>
                    <div class="d-flex align-items-center bg-light rounded-pill px-2">
                        <button class="btn btn-link text-dark px-2 mb-0" wire:click="updateQty({{ $id }}, -1)">-</button>
                        <span class="text-sm font-weight-bold mx-1">{{ $item['qty'] }}</span>
                        <button class="btn btn-link text-dark px-2 mb-0" wire:click="updateQty({{ $id }}, 1)">+</button>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="pt-3">
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-secondary font-weight-bold">Total Pembayaran</span>
                    <span class="h4 font-weight-black text-primary">${{ number_format($this->total, 2) }}</span>
                </div>
                <button wire:click="checkout" class="btn btn-primary w-100 btn-lg border-radius-lg py-3">PESAN SEKARANG</button>
            </div>
        </div>
    </div>
    @endif

    @if($orderPlaced)
    <div class="position-fixed top-50 start-50 translate-middle bg-white p-5 border-radius-2xl shadow-2xl text-center animate__animated animate__zoomIn" style="z-index: 2000; width: 90%;">
        <div class="bg-success-soft rounded-circle d-inline-flex p-3 mb-4">
            <i class="material-icons text-success text-5xl">verified</i>
        </div>
        <h3 class="font-weight-black">Pesanan Terkirim!</h3>
        <p class="text-secondary">Mohon tunggu sebentar di meja {{ $tableId }}, roti sedang kami siapkan.</p>
        <button wire:click="$set('orderPlaced', false)" class="btn btn-primary w-100 mt-3 rounded-pill">Selesai</button>
    </div>
    @endif
</div>