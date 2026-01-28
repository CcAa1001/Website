{{-- Product Card Grid (Livewire) - With Add to Cart --}}

<div class="product_card">
    <div class="product_img">
        <a href="{{ route('shop.show', $product->slug) }}" wire:navigate>
        
            <x-product-image 
    :product="$product" 
    size="medium"
    class="img-fluid w-100"
    loading="lazy" 
/>
        </a>

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
                <a href="{{ route('shop.show', $product->slug) }}" class="add_cart_btn" wire:navigate>
                    <i class="fas fa-cog"></i> Pilih Opsi
                </a>
            @else
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
            <a href="{{ route('shop.show', $product->slug) }}" wire:navigate>
                {{ Str::limit($product->name, 40) }}
            </a>
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

<style>
[x-cloak] { display: none !important; }

.product_card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    margin-bottom: 15px;
    transition: all 0.25s ease;
}
.product_card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transform: translateY(-3px);
}

.product_img {
    position: relative;
    overflow: hidden;
    aspect-ratio: 1;
}
.product_img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}
.product_card:hover .product_img img { transform: scale(1.05); }

.product_badges {
    position: absolute;
    top: 10px;
    left: 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.product_badges .badge {
    padding: 4px 10px;
    font-size: 10px;
    font-weight: 600;
    border-radius: 5px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.badge_featured { background: var(--primary-color, #ff6b6b); color: #fff; }
.badge_new { background: #28a745; color: #fff; }

.product_actions {
    position: absolute;
    top: 10px;
    right: -45px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: right 0.25s ease;
}
.product_card:hover .product_actions { right: 10px; }

.action_btn {
    width: 34px;
    height: 34px;
    background: #fff;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    color: #666;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.2s;
}
.action_btn:hover {
    background: var(--primary-color, #ff6b6b);
    color: #fff;
    transform: scale(1.1);
}

.add_cart_wrapper {
    position: absolute;
    bottom: -50px;
    left: 10px;
    right: 10px;
    transition: bottom 0.25s ease;
}
.product_card:hover .add_cart_wrapper { bottom: 10px; }

.add_cart_btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 10px;
    background: linear-gradient(135deg, var(--primary-color, #ff6b6b) 0%, #ff8e53 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 3px 10px rgba(255, 107, 107, 0.3);
}
.add_cart_btn:hover { 
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
    color: #fff; 
}
.add_cart_btn.adding {
    background: #28a745;
    pointer-events: none;
}

.product_info { padding: 14px; }

.product_category {
    font-size: 10px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

.product_name {
    font-size: 14px;
    font-weight: 600;
    margin: 6px 0;
    line-height: 1.35;
    height: 38px;
    overflow: hidden;
}
.product_name a { color: #333; text-decoration: none; }
.product_name a:hover { color: var(--primary-color, #ff6b6b); }

.product_price {
    display: flex;
    align-items: baseline;
    gap: 6px;
    flex-wrap: wrap;
}
.current_price {
    font-size: 16px;
    font-weight: 700;
    color: var(--primary-color, #ff6b6b);
}
.price_from { 
    font-size: 10px; 
    color: #999;
    font-weight: 400;
}

.product_tags { 
    margin-top: 10px; 
    display: flex; 
    gap: 5px; 
    flex-wrap: wrap;
}
.product_tags .tag {
    font-size: 10px;
    padding: 3px 8px;
    background: #f5f5f5;
    border-radius: 10px;
    color: #666;
}

/* Mobile Touch Friendly */
@media (max-width: 767px) {
    .product_actions { 
        right: 8px; 
        opacity: 1;
        top: 8px;
    }
    .add_cart_wrapper { 
        bottom: 8px; 
        opacity: 1;
        left: 8px;
        right: 8px;
    }
    .product_info { padding: 12px; }
    .product_name { font-size: 13px; height: 35px; }
    .current_price { font-size: 15px; }
    .action_btn {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
    .add_cart_btn {
        padding: 9px;
        font-size: 12px;
    }
}
</style>