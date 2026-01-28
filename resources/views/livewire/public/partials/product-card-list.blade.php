{{-- Product Card List (Livewire) --}}

<div class="product_card_list">
    <div class="row g-0">
        <div class="col-4 col-md-3">
            <div class="product_img">
            
    <a href="{{ route('shop.show', $product->slug) }}" wire:navigate>
        <x-product-image 
            :product="$product" 
            size="medium"
            class="img-fluid w-100 primary_img"
            loading="lazy" 
        />
    </a>
</div>
        </div>
        <div class="col-8 col-md-9">
            <div class="product_info">
                @if($product->category)
                    <span class="product_category">{{ $product->category->name }}</span>
                @endif

                <h3 class="product_name">
                    <a href="{{ route('shop.show', $product->slug) }}" wire:navigate>
                        {{ $product->name }}
                    </a>
                </h3>

                <p class="product_desc d-none d-md-block">
                    {{ Str::limit($product->description, 100) }}
                </p>

                <div class="product_meta">
                    <div class="product_price">
                        <span class="current_price">{{ $product->formatted_price }}</span>
                        @if($product->variants && $product->variants->count() > 0)
                            <span class="price_from">from</span>
                        @endif
                    </div>

                    @if($product->tags && count($product->tags) > 0)
                        <div class="product_tags d-none d-sm-flex">
                            @foreach(array_slice($product->tags, 0, 3) as $tag)
                                <span class="tag">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="product_actions">
                    @if($product->variants && $product->variants->count() > 0)
                        <a href="{{ route('shop.show', $product->slug) }}" class="btn_add" wire:navigate>
                            <i class="fas fa-cog"></i> Options
                        </a>
                    @else
                        <button type="button" class="btn_add">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    @endif
                    <button type="button" class="btn_wishlist">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product_card_list {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    margin-bottom: 15px;
    transition: all 0.2s;
}
.product_card_list:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.product_card_list .product_img {
    position: relative;
    height: 100%;
    min-height: 120px;
}
.product_card_list .product_img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.product_card_list .badge_featured {
    position: absolute;
    top: 8px;
    left: 8px;
    padding: 3px 8px;
    font-size: 10px;
    background: var(--primary-color, #ff6b6b);
    color: #fff;
    border-radius: 4px;
}

.product_card_list .product_info {
    padding: 15px;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.product_card_list .product_category {
    font-size: 11px;
    color: #888;
    text-transform: uppercase;
}

.product_card_list .product_name {
    font-size: 16px;
    font-weight: 600;
    margin: 5px 0 10px;
}
.product_card_list .product_name a {
    color: #333;
    text-decoration: none;
}
.product_card_list .product_name a:hover {
    color: var(--primary-color, #ff6b6b);
}

.product_card_list .product_desc {
    font-size: 13px;
    color: #666;
    margin-bottom: 10px;
    flex-grow: 1;
}

.product_card_list .product_meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.product_card_list .current_price {
    font-size: 18px;
    font-weight: 600;
    color: var(--primary-color, #ff6b6b);
}
.product_card_list .price_from {
    font-size: 11px;
    color: #999;
    margin-left: 5px;
}

.product_card_list .product_tags {
    display: flex;
    gap: 5px;
}
.product_card_list .tag {
    font-size: 10px;
    padding: 3px 8px;
    background: #f0f0f0;
    border-radius: 10px;
    color: #666;
}

.product_card_list .product_actions {
    display: flex;
    gap: 10px;
}

.product_card_list .btn_add {
    flex: 1;
    padding: 8px 15px;
    background: var(--primary-color, #ff6b6b);
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
}
.product_card_list .btn_add:hover {
    background: #e55656;
    color: #fff;
}

.product_card_list .btn_wishlist {
    width: 40px;
    height: 40px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.product_card_list .btn_wishlist:hover {
    border-color: var(--primary-color, #ff6b6b);
    color: var(--primary-color, #ff6b6b);
}

@media (max-width: 575px) {
    .product_card_list .product_info { padding: 10px; }
    .product_card_list .product_name { font-size: 14px; margin-bottom: 5px; }
    .product_card_list .current_price { font-size: 15px; }
    .product_card_list .btn_add { padding: 6px 10px; font-size: 12px; }
    .product_card_list .btn_wishlist { width: 34px; height: 34px; }
}
</style>
