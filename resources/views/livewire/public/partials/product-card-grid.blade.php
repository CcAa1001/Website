{{-- Product Card Grid (Livewire) - QR Menu Version --}}

<div class="product_card">
    <div class="product_img">

        {{-- IMAGE CLICK --}}
        <button
            type="button"
            class="border-0 bg-transparent p-0 w-100"
            wire:click="$dispatch('openProductModal', { productId: '{{ $product->id }}' })"
        >
            <x-product-image 
                :product="$product" 
                size="medium"
                class="img-fluid w-100"
                loading="lazy" 
            />
        </button>

        {{-- Badges --}}
        <div class="product_badges">
            @if($product->is_featured)
                <span class="badge badge_featured">Featured</span>
            @endif

            @if($product->created_at && $product->created_at->isAfter(now()->subDays(7)))
                <span class="badge badge_new">New</span>
            @endif
        </div>

        {{-- Add to Cart --}}
        <div class="add_cart_wrapper">
            @if($product->variants && $product->variants->count() > 0)
                {{-- HAS VARIANT --}}
                <button
                    type="button"
                    class="add_cart_btn"
                    wire:click="$dispatch('openProductModal', { productId: '{{ $product->id }}' })"
                >
                    <i class="fas fa-cog"></i> Pilih Opsi
                </button>
            @else
                {{-- SIMPLE PRODUCT --}}
                <button 
                    type="button" 
                    class="add_cart_btn"
                    x-data="{ loading: false }"
                    @click="
                        loading = true;
                        $dispatch('add-to-cart', { 
                            productId: '{{ $product->id }}',
                            variantId: null,
                            quantity: 1,
                            modifiers: [],
                            notes: null
                        });
                        setTimeout(() => loading = false, 500);
                    "
                    :class="{ 'adding': loading }"
                >
                    <span x-show="!loading">
                        <i class="fas fa-cart-plus"></i> Tambah
                    </span>
                    <span x-show="loading" x-cloak>
                        <i class="fas fa-check"></i> Ditambahkan
                    </span>
                </button>
            @endif
        </div>
    </div>

    <div class="product_info">
        @if($product->category)
            <span class="product_category">{{ $product->category->name }}</span>
        @endif

        <h3 class="product_name">
            <button
                type="button"
                class="border-0 bg-transparent p-0 text-start"
                wire:click="$dispatch('openProductModal', { productId: '{{ $product->id }}' })"
            >
                {{ Str::limit($product->name, 40) }}
            </button>
        </h3>

        <div class="product_price">
            <span class="current_price">{{ $product->formatted_price }}</span>

            @if($product->variants && $product->variants->count() > 0)
                @php $maxAdjustment = $product->variants->max('price_adjustment'); @endphp
                @if($maxAdjustment > 0)
                    <span class="price_from">mulai dari</span>
                @endif
            @endif
        </div>

        @if($product->tags && count($product->tags) > 0)
            <div class="product_tags">
                @foreach(array_slice($product->tags, 0, 2) as $tag)
                    <span class="tag">{{ $tag }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>
