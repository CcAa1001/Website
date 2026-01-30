<div class="main_content_wrapper">
    @if($orderSuccess)
        <div class="success-screen">
            <div class="success-icon animate__animated animate__bounceIn">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>Pesanan Diterima!</h3>
            <p>Nomor Order: <strong>{{ $orderNumberStr }}</strong></p>
            <p class="text-muted">Mohon tunggu, pesanan Anda sedang disiapkan.</p>
            <button wire:click="$set('orderSuccess', false)" class="btn btn-primary rounded-pill mt-3 px-4">
                Pesan Lagi
            </button>
        </div>
    @else
        {{-- Sticky Category Header --}}
        <div class="sticky_category_header">
            <div class="d-flex justify-content-between align-items-center mb-3 px-3 pt-3">
                <div>
                    <h5 class="font-weight-bold mb-0 text-dark">Menu Digital</h5>
                    <span class="text-xs text-muted">Meja {{ $tableNumber }}</span>
                </div>
                <button class="cart-icon-btn" data-bs-toggle="modal" data-bs-target="#cartModal">
                    <i class="fas fa-shopping-basket"></i>
                    @if(count($cart) > 0)
                        <span class="cart-badge">{{ count($cart) }}</span>
                    @endif
                </button>
            </div>

            {{-- Horizontal Category Scroll --}}
            <div class="category-scroll-wrapper px-3 pb-2">
                <button wire:click="selectCategory('all')" 
                        class="cat-pill {{ $selectedCategory == 'all' ? 'active' : '' }}">
                    🔥 Semua
                </button>
                @foreach($categories as $cat)
                    <button wire:click="selectCategory('{{ $cat->id }}')" 
                            class="cat-pill {{ $selectedCategory == $cat->id ? 'active' : '' }}">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Product Grid --}}
        <div class="container-fluid px-3 pb-5" style="margin-top: 130px;">
            <div class="row g-3">
                @forelse($products as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card-modern" wire:click="addToCart('{{ $product->id }}')">
                        <div class="image-box">
                            @if($product->image_url)
                                <img src="{{ asset('storage/' . $product->image_url) }}" alt="{{ $product->name }}">
                            @else
                                <div class="no-image">
                                    <i class="fas fa-utensils"></i>
                                </div>
                            @endif
                            <button class="add-btn-floating">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="card-details">
                            <h6 class="product-name">{{ $product->name }}</h6>
                            <div class="price text-primary">Rp {{ number_format($product->base_price, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5 mt-5">
                    <div class="opacity-50 mb-3">
                        <i class="fas fa-search fa-3x text-secondary"></i>
                    </div>
                    <p class="text-muted">Item tidak ditemukan di kategori ini.</p>
                </div>
                @endforelse
            </div>
        </div>
    @endif
</div>