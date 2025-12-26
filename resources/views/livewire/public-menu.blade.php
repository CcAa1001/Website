<div class="bg-gray-100 min-h-screen pb-5">
    <!-- Premium Header -->
    <nav class="navbar navbar-expand-lg blur border-radius-lg top-0 z-index-3 shadow position-sticky py-2 mx-4">
        <div class="container-fluid">
            <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3" href="">
                ARTISAN BAKERY
            </a>
            <div class="ms-auto">
                <button type="button" class="btn btn-sm btn-round mb-0 me-1 bg-gradient-dark" wire:click="$set('showCart', true)">
                    <i class="material-icons text-sm">shopping_basket</i>
                    <span class="ms-1">{{ count($cart) }} Items</span>
                </button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h6 class="text-primary font-weight-bold text-uppercase">Welcome to Table {{ $tableId ?? 'Walk-in' }}</h6>
                <h2 class="font-weight-black">Freshly Baked Today</h2>
                
                <!-- Category Filter -->
                <div class="d-flex justify-content-center gap-2 mt-3 overflow-auto pb-2">
                    @foreach(['All', 'Bread', 'Pastry', 'Cakes', 'Drinks'] as $cat)
                        <button wire:click="setCategory('{{ $cat }}')" 
                            class="btn btn-sm {{ $category == $cat ? 'btn-primary' : 'btn-outline-primary' }} btn-round mb-0 whitespace-nowrap">
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="row mt-2">
            @forelse($products as $product)
            <div class="col-md-3 col-6 mb-4">
                <div class="card h-100 shadow-sm border-radius-lg overflow-hidden">
                    <div class="position-relative">
                        <img src="{{ $product->image ?? asset('assets/img/products/product-details-1.jpg') }}" class="w-100" style="height: 180px; object-fit: cover;">
                        <span class="badge bg-white text-dark position-absolute top-0 end-0 m-2 shadow-sm">
                            ${{ number_format($product->price, 2) }}
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <h6 class="mb-0 font-weight-bold text-truncate">{{ $product->name }}</h6>
                        <p class="text-xs text-secondary mb-3 text-truncate">{{ $product->description }}</p>
                        <button wire:click="addToCart({{ $product->id }})" class="btn btn-sm btn-outline-dark w-100 mb-0 py-2 border-radius-md">
                            <i class="material-icons text-xs">add</i> Add
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <p class="text-secondary">No products found in this category.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Order Success Modal -->
    @if($orderPlaced)
    <div class="position-fixed top-5 start-50 translate-middle-x z-index-5">
        <div class="alert alert-success text-white font-weight-bold py-3 px-5 border-radius-xl shadow-lg animate__animated animate__fadeInDown">
            <span class="alert-icon"><i class="material-icons">check_circle</i></span>
            <span class="alert-text ms-2">Order received for Table {{ $tableId }}! We're preparing it now.</span>
        </div>
    </div>
    @endif

    <!-- Cart Sidebar (Offcanvas style) -->
    @if($showCart)
    <div class="fixed-plugin show">
        <div class="card shadow-lg">
            <div class="card-header pb-0 pt-3">
                <div class="float-start">
                    <h5 class="mt-3 mb-0">Your Order</h5>
                    <p>Table: {{ $tableId ?? 'Walk-in' }}</p>
                </div>
                <div class="float-end mt-4">
                    <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" wire:click="$set('showCart', false)">
                        <i class="material-icons">clear</i>
                    </button>
                </div>
            </div>
            <hr class="horizontal dark my-1">
            <div class="card-body pt-sm-3 pt-0 overflow-auto" style="max-height: 60vh;">
                @forelse($cart as $id => $item)
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ $item['image'] }}" class="avatar avatar-md border-radius-lg shadow-sm me-3">
                    <div class="flex-grow-1">
                        <h6 class="text-sm mb-0">{{ $item['name'] }}</h6>
                        <p class="text-xs mb-0">${{ number_format($item['price'] * $item['qty'], 2) }}</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-xs btn-outline-secondary mb-0 px-2" wire:click="updateQty({{ $id }}, -1)">-</button>
                        <span class="mx-2 text-xs font-weight-bold">{{ $item['qty'] }}</span>
                        <button class="btn btn-xs btn-outline-secondary mb-0 px-2" wire:click="updateQty({{ $id }}, 1)">+</button>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 opacity-5">
                    <i class="material-icons style-4xl">shopping_basket</i>
                    <p class="font-weight-bold mt-2">Empty Basket</p>
                </div>
                @endforelse
            </div>
            @if(count($cart) > 0)
            <div class="card-footer bg-gray-100">
                <div class="d-flex justify-content-between mb-3">
                    <span class="h6 mb-0">Total</span>
                    <span class="h6 mb-0 font-weight-black">${{ number_format($this->total, 2) }}</span>
                </div>
                <button wire:click="checkout" class="btn bg-gradient-dark w-100 py-3 border-radius-lg">
                    Confirm Order
                </button>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>