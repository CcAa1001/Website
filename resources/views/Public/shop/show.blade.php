{{--
    Product Detail Page
    Shows product info, variants, modifiers, and related products
--}}

@extends('layouts.public')

@section('title', $product->name)

@section('content')

    {{-- Breadcrumb --}}
    <section class="breadcrumb_section">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('shop.index') }}">Shop</a></li>
                    @if($product->category)
                        <li class="breadcrumb-item">
                            <a href="{{ route('shop.category', $product->category->slug) }}">
                                {{ $product->category->name }}
                            </a>
                        </li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($product->name, 30) }}</li>
                </ol>
            </nav>
        </div>
    </section>

    {{-- Product Detail Section --}}
    <section class="product_detail_section py-4">
        <div class="container">
            <div class="row">
                
                {{-- Product Image --}}
                <div class="col-lg-5 col-md-6 mb-4">
                    <div class="product_image_wrapper">
                        <div class="main_image">
                            @if($product->image_url)
                                <img src="{{ $product->image }}" 
                                     alt="{{ $product->name }}" 
                                     class="img-fluid w-100"
                                     id="mainProductImage">
                            @else
                                <img src="{{ asset('assets/images/product_placeholder.png') }}" 
                                     alt="{{ $product->name }}" 
                                     class="img-fluid w-100"
                                     id="mainProductImage">
                            @endif
                            
                            {{-- Badges --}}
                            <div class="product_badges">
                                @if($product->is_featured)
                                    <span class="badge badge_featured">Featured</span>
                                @endif
                                @if($product->created_at && $product->created_at->isAfter(now()->subDays(7)))
                                    <span class="badge badge_new">New</span>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Thumbnail Gallery (if multiple images exist) --}}
                        @if($product->gallery && count($product->gallery) > 0)
                            <div class="thumbnail_gallery mt-3">
                                <div class="row g-2">
                                    <div class="col-3">
                                        <img src="{{ $product->image }}" 
                                             alt="{{ $product->name }}"
                                             class="img-fluid thumbnail active"
                                             onclick="changeMainImage(this)">
                                    </div>
                                    @foreach($product->gallery as $image)
                                        <div class="col-3">
                                            <img src="{{ Storage::url($image) }}" 
                                                 alt="{{ $product->name }}"
                                                 class="img-fluid thumbnail"
                                                 onclick="changeMainImage(this)">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Product Info --}}
                <div class="col-lg-7 col-md-6">
                    <div class="product_info_wrapper">
                        
                        {{-- Category --}}
                        @if($product->category)
                            <a href="{{ route('shop.category', $product->category->slug) }}" class="product_category">
                                {{ $product->category->name }}
                            </a>
                        @endif

                        {{-- Product Name --}}
                        <h1 class="product_name">{{ $product->name }}</h1>

                        {{-- Rating --}}
                        <div class="product_rating mb-3">
                            <div class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span class="rating_text">(0 Reviews)</span>
                            @if($product->sku)
                                <span class="sku">SKU: {{ $product->sku }}</span>
                            @endif
                        </div>

                        {{-- Price --}}
                        <div class="product_price mb-3">
                            <span class="current_price" id="displayPrice">{{ $product->formatted_price }}</span>
                            @if($product->variants && $product->variants->count() > 0)
                                @php
                                    $minPrice = $product->base_price + $product->variants->min('price_adjustment');
                                    $maxPrice = $product->base_price + $product->variants->max('price_adjustment');
                                @endphp
                                @if($minPrice !== $maxPrice)
                                    <span class="price_range">
                                        Rp {{ number_format($minPrice, 0, ',', '.') }} - Rp {{ number_format($maxPrice, 0, ',', '.') }}
                                    </span>
                                @endif
                            @endif
                        </div>

                        {{-- Availability --}}
                        <div class="availability mb-3">
                            @if($product->is_available)
                                <span class="in_stock"><i class="fas fa-check-circle"></i> In Stock</span>
                            @else
                                <span class="out_of_stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                            @endif
                        </div>

                        {{-- Short Description --}}
                        @if($product->description)
                            <div class="short_description mb-4">
                                <p>{{ Str::limit(strip_tags($product->description), 200) }}</p>
                            </div>
                        @endif

                        {{-- Tags --}}
                        @if($product->tags && count($product->tags) > 0)
                            <div class="product_tags mb-4">
                                @foreach($product->tags as $tag)
                                    <span class="tag">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif

                        <hr>

                        {{-- Add to Cart Form --}}
                        <form id="addToCartForm" class="add_to_cart_form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="base_price" id="basePrice" value="{{ $product->base_price }}">

                            {{-- Variants --}}
                            @if($product->variants && $product->variants->count() > 0)
                                <div class="variants_section mb-4">
                                    <h6 class="section_title">Select Variant</h6>
                                    <div class="variant_options">
                                        @foreach($product->variants as $index => $variant)
                                            <label class="variant_option {{ $index === 0 ? 'active' : '' }}">
                                                <input type="radio" 
                                                       name="variant_id" 
                                                       value="{{ $variant->id }}"
                                                       data-price-adjustment="{{ $variant->price_adjustment }}"
                                                       data-name="{{ $variant->name }}"
                                                       {{ $index === 0 ? 'checked' : '' }}>
                                                <span class="variant_label">
                                                    <span class="variant_name">{{ $variant->name }}</span>
                                                    @if($variant->price_adjustment > 0)
                                                        <span class="variant_price">+Rp {{ number_format($variant->price_adjustment, 0, ',', '.') }}</span>
                                                    @elseif($variant->price_adjustment < 0)
                                                        <span class="variant_price discount">-Rp {{ number_format(abs($variant->price_adjustment), 0, ',', '.') }}</span>
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Modifiers --}}
                            @if($product->modifierGroups && $product->modifierGroups->count() > 0)
                                <div class="modifiers_section mb-4">
                                    @foreach($product->modifierGroups as $group)
                                        <div class="modifier_group mb-3">
                                            <h6 class="section_title">
                                                {{ $group->name }}
                                                @if($group->is_required)
                                                    <span class="required_badge">Required</span>
                                                @else
                                                    <span class="optional_badge">Optional</span>
                                                @endif
                                                @if($group->max_selections > 1)
                                                    <small class="text-muted">(Max {{ $group->max_selections }})</small>
                                                @endif
                                            </h6>
                                            
                                            <div class="modifier_options">
                                                @foreach($group->modifiers as $modifier)
                                                    <label class="modifier_option">
                                                        @if($group->max_selections === 1)
                                                            <input type="radio" 
                                                                   name="modifiers[{{ $group->id }}]" 
                                                                   value="{{ $modifier->id }}"
                                                                   data-price="{{ $modifier->price }}"
                                                                   {{ $group->is_required && $loop->first ? 'checked' : '' }}>
                                                        @else
                                                            <input type="checkbox" 
                                                                   name="modifiers[{{ $group->id }}][]" 
                                                                   value="{{ $modifier->id }}"
                                                                   data-price="{{ $modifier->price }}"
                                                                   data-group="{{ $group->id }}"
                                                                   data-max="{{ $group->max_selections }}">
                                                        @endif
                                                        <span class="modifier_label">
                                                            <span class="modifier_name">{{ $modifier->name }}</span>
                                                            @if($modifier->price > 0)
                                                                <span class="modifier_price">+Rp {{ number_format($modifier->price, 0, ',', '.') }}</span>
                                                            @endif
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Special Instructions --}}
                            <div class="special_instructions mb-4">
                                <h6 class="section_title">Special Instructions (Optional)</h6>
                                <textarea name="notes" 
                                          class="form-control" 
                                          rows="2" 
                                          placeholder="E.g., No onions, extra spicy, etc."></textarea>
                            </div>

                            {{-- Quantity & Add to Cart --}}
                            <div class="quantity_cart_wrapper">
                                <div class="quantity_selector">
                                    <button type="button" class="qty_btn minus" onclick="changeQuantity(-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           name="quantity" 
                                           id="quantity" 
                                           value="1" 
                                           min="1" 
                                           max="99"
                                           readonly>
                                    <button type="button" class="qty_btn plus" onclick="changeQuantity(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <button type="submit" class="btn_add_to_cart" {{ !$product->is_available ? 'disabled' : '' }}>
                                    <i class="fas fa-shopping-cart"></i>
                                    <span>Add to Cart</span>
                                    <span class="total_price" id="totalPrice">{{ $product->formatted_price }}</span>
                                </button>
                            </div>
                        </form>

                        {{-- Wishlist & Share --}}
                        <div class="extra_actions mt-4">
                            <button class="action_btn wishlist_btn" data-product-id="{{ $product->id }}">
                                <i class="far fa-heart"></i> Add to Wishlist
                            </button>
                            <button class="action_btn share_btn" onclick="shareProduct()">
                                <i class="fas fa-share-alt"></i> Share
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Product Details Tabs --}}
    <section class="product_tabs_section py-4">
        <div class="container">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" 
                            data-bs-target="#description" type="button" role="tab">
                        Description
                    </button>
                </li>
                @if($product->nutrition_info)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="nutrition-tab" data-bs-toggle="tab" 
                                data-bs-target="#nutrition" type="button" role="tab">
                            Nutrition Info
                        </button>
                    </li>
                @endif
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" 
                            data-bs-target="#reviews" type="button" role="tab">
                        Reviews (0)
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="productTabsContent">
                {{-- Description Tab --}}
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <div class="description_content">
                        @if($product->description)
                            {!! nl2br(e($product->description)) !!}
                        @else
                            <p class="text-muted">No description available.</p>
                        @endif
                    </div>
                </div>
                
                {{-- Nutrition Tab --}}
                @if($product->nutrition_info)
                    <div class="tab-pane fade" id="nutrition" role="tabpanel">
                        <div class="nutrition_content">
                            <table class="table table-striped">
                                <tbody>
                                    @foreach($product->nutrition_info as $key => $value)
                                        <tr>
                                            <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                            <td><strong>{{ $value }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                
                {{-- Reviews Tab --}}
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <div class="reviews_content">
                        <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                        {{-- Review form can be added here --}}
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Related Products --}}
    @if($relatedProducts->count() > 0)
        <section class="related_products_section py-5 bg-light">
            <div class="container">
                <h3 class="section_title mb-4">Related Products</h3>
                <div class="row g-3">
                    @foreach($relatedProducts as $related)
                        <div class="col-6 col-md-4 col-lg-3">
                            @include('public.shop.partials.product-card-grid', ['product' => $related])
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection

@push('styles')
<style>
/* Breadcrumb */
.breadcrumb_section {
    background: #f8f9fa;
    padding: 15px 0;
}
.breadcrumb_section .breadcrumb-item a {
    color: #666;
    text-decoration: none;
}
.breadcrumb_section .breadcrumb-item a:hover {
    color: var(--primary-color, #ff6b6b);
}
.breadcrumb_section .breadcrumb-item.active {
    color: #333;
}

/* Product Image */
.product_image_wrapper {
    position: sticky;
    top: 20px;
}
.main_image {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    background: #f8f9fa;
}
.main_image img {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
}
.product_badges {
    position: absolute;
    top: 15px;
    left: 15px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.product_badges .badge {
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 500;
    border-radius: 4px;
}
.badge_featured { background: var(--primary-color, #ff6b6b); color: #fff; }
.badge_new { background: #28a745; color: #fff; }

.thumbnail_gallery .thumbnail {
    border-radius: 8px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s;
    aspect-ratio: 1;
    object-fit: cover;
}
.thumbnail_gallery .thumbnail:hover,
.thumbnail_gallery .thumbnail.active {
    border-color: var(--primary-color, #ff6b6b);
}

/* Product Info */
.product_info_wrapper .product_category {
    font-size: 13px;
    color: var(--primary-color, #ff6b6b);
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.product_info_wrapper .product_name {
    font-size: 28px;
    font-weight: 700;
    color: #333;
    margin: 10px 0 15px;
}
.product_rating {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.product_rating .stars { color: #ffc107; font-size: 14px; }
.product_rating .rating_text { color: #666; font-size: 14px; }
.product_rating .sku { 
    color: #999; 
    font-size: 13px;
    padding-left: 10px;
    border-left: 1px solid #ddd;
}

.product_price .current_price {
    font-size: 32px;
    font-weight: 700;
    color: var(--primary-color, #ff6b6b);
}
.product_price .price_range {
    font-size: 14px;
    color: #888;
    margin-left: 10px;
}

.availability .in_stock { color: #28a745; font-weight: 500; }
.availability .out_of_stock { color: #dc3545; font-weight: 500; }

.short_description p { color: #666; line-height: 1.7; }

.product_tags .tag {
    display: inline-block;
    padding: 5px 12px;
    background: #f0f0f0;
    border-radius: 20px;
    font-size: 12px;
    color: #666;
    margin-right: 5px;
    margin-bottom: 5px;
}

/* Variants */
.section_title {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 12px;
}
.required_badge {
    font-size: 10px;
    padding: 2px 6px;
    background: #dc3545;
    color: #fff;
    border-radius: 3px;
    margin-left: 5px;
}
.optional_badge {
    font-size: 10px;
    padding: 2px 6px;
    background: #6c757d;
    color: #fff;
    border-radius: 3px;
    margin-left: 5px;
}

.variant_options {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.variant_option {
    cursor: pointer;
}
.variant_option input { display: none; }
.variant_label {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px 20px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    transition: all 0.2s;
    min-width: 100px;
    text-align: center;
}
.variant_option input:checked + .variant_label,
.variant_option:hover .variant_label {
    border-color: var(--primary-color, #ff6b6b);
    background: rgba(255, 107, 107, 0.05);
}
.variant_option input:checked + .variant_label {
    background: rgba(255, 107, 107, 0.1);
}
.variant_name { font-weight: 500; color: #333; }
.variant_price { font-size: 12px; color: var(--primary-color, #ff6b6b); margin-top: 4px; }
.variant_price.discount { color: #28a745; }

/* Modifiers */
.modifier_options {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.modifier_option {
    cursor: pointer;
}
.modifier_option input { display: none; }
.modifier_label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    transition: all 0.2s;
}
.modifier_option input:checked + .modifier_label {
    border-color: var(--primary-color, #ff6b6b);
    background: rgba(255, 107, 107, 0.05);
}
.modifier_option input:checked + .modifier_label::before {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    margin-right: 10px;
    color: var(--primary-color, #ff6b6b);
}
.modifier_name { font-size: 14px; color: #333; }
.modifier_price { font-size: 13px; color: var(--primary-color, #ff6b6b); font-weight: 500; }

/* Special Instructions */
.special_instructions textarea {
    border-radius: 8px;
    border-color: #e0e0e0;
    resize: none;
}
.special_instructions textarea:focus {
    border-color: var(--primary-color, #ff6b6b);
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
}

/* Quantity & Add to Cart */
.quantity_cart_wrapper {
    display: flex;
    gap: 15px;
    align-items: stretch;
}
.quantity_selector {
    display: flex;
    align-items: center;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}
.qty_btn {
    width: 45px;
    height: 50px;
    border: none;
    background: #f8f9fa;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}
.qty_btn:hover { background: #e9ecef; }
.quantity_selector input {
    width: 50px;
    height: 50px;
    border: none;
    text-align: center;
    font-size: 16px;
    font-weight: 600;
}
.btn_add_to_cart {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px 25px;
    background: var(--primary-color, #ff6b6b);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.btn_add_to_cart:hover { background: #e55656; }
.btn_add_to_cart:disabled { background: #ccc; cursor: not-allowed; }
.btn_add_to_cart .total_price {
    padding-left: 10px;
    border-left: 1px solid rgba(255,255,255,0.3);
}

/* Extra Actions */
.extra_actions {
    display: flex;
    gap: 15px;
}
.extra_actions .action_btn {
    padding: 10px 20px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: #fff;
    cursor: pointer;
    font-size: 14px;
    color: #666;
    transition: all 0.2s;
}
.extra_actions .action_btn:hover {
    border-color: var(--primary-color, #ff6b6b);
    color: var(--primary-color, #ff6b6b);
}

/* Tabs */
.product_tabs_section .nav-tabs {
    border-bottom: 2px solid #e9ecef;
}
.product_tabs_section .nav-link {
    border: none;
    color: #666;
    font-weight: 500;
    padding: 12px 25px;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
}
.product_tabs_section .nav-link:hover {
    color: var(--primary-color, #ff6b6b);
}
.product_tabs_section .nav-link.active {
    color: var(--primary-color, #ff6b6b);
    border-bottom-color: var(--primary-color, #ff6b6b);
}
.tab-content {
    padding: 25px 0;
}
.description_content { line-height: 1.8; color: #555; }

/* Related Products */
.related_products_section .section_title {
    font-size: 22px;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 767px) {
    .product_info_wrapper .product_name { font-size: 22px; }
    .product_price .current_price { font-size: 26px; }
    .quantity_cart_wrapper { flex-direction: column; }
    .quantity_selector { justify-content: center; }
    .btn_add_to_cart { width: 100%; }
    .extra_actions { flex-wrap: wrap; }
    .extra_actions .action_btn { flex: 1; text-align: center; }
    .product_tabs_section .nav-link { padding: 10px 15px; font-size: 14px; }
}
</style>
@endpush

@push('scripts')
<script>
// Change main image (for gallery)
function changeMainImage(thumbnail) {
    document.getElementById('mainProductImage').src = thumbnail.src;
    document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
    thumbnail.classList.add('active');
}

// Change quantity
function changeQuantity(delta) {
    const input = document.getElementById('quantity');
    let value = parseInt(input.value) + delta;
    if (value < 1) value = 1;
    if (value > 99) value = 99;
    input.value = value;
    updateTotalPrice();
}

// Calculate and update total price
function updateTotalPrice() {
    const basePrice = parseFloat(document.getElementById('basePrice').value);
    const quantity = parseInt(document.getElementById('quantity').value);
    
    // Get variant price adjustment
    let variantAdjustment = 0;
    const selectedVariant = document.querySelector('input[name="variant_id"]:checked');
    if (selectedVariant) {
        variantAdjustment = parseFloat(selectedVariant.dataset.priceAdjustment) || 0;
    }
    
    // Get modifier prices
    let modifierTotal = 0;
    document.querySelectorAll('.modifier_option input:checked').forEach(input => {
        modifierTotal += parseFloat(input.dataset.price) || 0;
    });
    
    // Calculate total
    const unitPrice = basePrice + variantAdjustment + modifierTotal;
    const totalPrice = unitPrice * quantity;
    
    // Update display
    document.getElementById('displayPrice').textContent = formatRupiah(unitPrice);
    document.getElementById('totalPrice').textContent = formatRupiah(totalPrice);
}

// Format number to Rupiah
function formatRupiah(number) {
    return 'Rp ' + number.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Variant selection
document.querySelectorAll('.variant_option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.variant_option').forEach(o => o.classList.remove('active'));
        this.classList.add('active');
        updateTotalPrice();
    });
});

// Modifier selection
document.querySelectorAll('.modifier_option input').forEach(input => {
    input.addEventListener('change', function() {
        // For checkboxes, enforce max selections
        if (this.type === 'checkbox') {
            const groupId = this.dataset.group;
            const maxSelections = parseInt(this.dataset.max);
            const checked = document.querySelectorAll(`input[data-group="${groupId}"]:checked`);
            
            if (checked.length > maxSelections) {
                this.checked = false;
                alert(`Maximum ${maxSelections} selection(s) allowed`);
                return;
            }
        }
        updateTotalPrice();
    });
});

// Form submission
document.getElementById('addToCartForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Collect form data
    const formData = new FormData(this);
    
    // TODO: Send to cart via AJAX
    console.log('Adding to cart:', Object.fromEntries(formData));
    
    // Show success message (placeholder)
    alert('Added to cart!');
});

// Share product
function shareProduct() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $product->name }}',
            text: '{{ Str::limit($product->description, 100) }}',
            url: window.location.href
        });
    } else {
        // Fallback: copy link
        navigator.clipboard.writeText(window.location.href);
        alert('Link copied to clipboard!');
    }
}

// Initialize price on page load
document.addEventListener('DOMContentLoaded', updateTotalPrice);
</script>
@endpush
