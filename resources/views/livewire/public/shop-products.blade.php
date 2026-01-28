<div class="shop_livewire_wrapper">
    
    {{-- Mobile Page Header --}}
    <div class="mobile_page_header d-md-none">
        <div class="container">
            <h1 class="page_title">
                @if($currentCategory)
                    {{ $currentCategory->name }}
                @elseif($search)
                    Search: "{{ $search }}"
                @else
                    Shop
                @endif
            </h1>
        </div>
    </div>

    {{-- Category Slider --}}
    <section class="fp_category_slider_section">
        <div class="container">
            <div class="fp_category_slider_wrapper">
                <div class="fp_category_slider" id="categorySlider">
                    
                    {{-- "All" Category --}}
                    <button type="button" 
                            wire:click="selectCategory(null)"
                            class="fp_category_item {{ !$selectedCategory ? 'active' : '' }}">
                        <div class="fp_cat_icon_box">
                            <i class="fas fa-th-large"></i>
                        </div>
                        <span class="fp_cat_name">All</span>
                    </button>

                    @foreach($sliderCategories as $cat)
                        <button type="button"
                                wire:click="selectCategory('{{ $cat->slug }}')"
                                class="fp_category_item {{ $selectedCategory === $cat->slug ? 'active' : '' }}">
                            <div class="fp_cat_icon_box">
                                @if($cat->image_url)
                                    <img src="{{ $cat->image }}" alt="{{ $cat->name }}" loading="lazy">
                                @else
                                    @switch($cat->slug)
                                        @case('makanan-utama')
                                            <i class="fas fa-utensils"></i>
                                            @break
                                        @case('minuman')
                                            <i class="fas fa-coffee"></i>
                                            @break
                                        @case('snack-appetizer')
                                            <i class="fas fa-cookie-bite"></i>
                                            @break
                                        @case('dessert')
                                            <i class="fas fa-ice-cream"></i>
                                            @break
                                        @case('paket-hemat')
                                            <i class="fas fa-box"></i>
                                            @break
                                        @case('nasi')
                                            <i class="fas fa-bowl-rice"></i>
                                            @break
                                        @case('mie')
                                            <i class="fas fa-bowl-food"></i>
                                            @break
                                        @case('ayam')
                                            <i class="fas fa-drumstick-bite"></i>
                                            @break
                                        @case('seafood')
                                            <i class="fas fa-fish"></i>
                                            @break
                                        @case('kopi')
                                            <i class="fas fa-mug-hot"></i>
                                            @break
                                        @case('teh')
                                            <i class="fas fa-mug-saucer"></i>
                                            @break
                                        @case('jus')
                                            <i class="fas fa-glass-water"></i>
                                            @break
                                        @case('smoothie')
                                            <i class="fas fa-blender"></i>
                                            @break
                                        @case('gorengan')
                                            <i class="fas fa-fire"></i>
                                            @break
                                        @case('dimsum')
                                            <i class="fas fa-bowl-food"></i>
                                            @break
                                        @case('es-krim')
                                            <i class="fas fa-ice-cream"></i>
                                            @break
                                        @case('kue')
                                            <i class="fas fa-cake-candles"></i>
                                            @break
                                        @default
                                            <i class="fas fa-tag"></i>
                                    @endswitch
                                @endif
                            </div>
                            <span class="fp_cat_name">{{ Str::limit($cat->name, 10) }}</span>
                        </button>
                    @endforeach

                </div>
            </div>
        </div>
    </section>

    {{-- Subcategories Pills --}}
    @if($subcategories->count() > 0)
    <section class="subcategories_section py-2">
        <div class="container">
            <div class="d-flex flex-wrap gap-2">
                @foreach($subcategories as $sub)
                    <button type="button" 
                            wire:click="selectCategory('{{ $sub->slug }}')"
                            class="subcategory_pill {{ $selectedCategory === $sub->slug ? 'active' : '' }}">
                        {{ $sub->name }}
                        <span class="count">({{ $sub->products_count }})</span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Main Shop Content --}}
    <section class="shop_page mt_25 mb_100">
        <div class="container">
            <div class="row">
                
                {{-- Sidebar Filters (Desktop) 
                <div class="col-xxl-2 col-lg-3 col-xl-3 d-none d-lg-block">
                    <div class="shop_sidebar">
                        @include('livewire.partials.shop-sidebar')
                    </div>
                </div> --}}

                {{-- Products Area --}}
                <div class="col-xxl-10 col-lg-9 col-xl-9 col-12">
                    
                    {{-- Top Bar --}}
                    @include('livewire.public.partials.shop-topbar')

                    {{-- Loading Indicator --}}
                    <div wire:loading.delay class="loading_overlay">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    {{-- Products Grid/List --}}
                    <div wire:loading.remove>
                        @if($products->count() > 0)
                            <div class="row g-2 g-sm-3" id="products-container">
                                @foreach($products as $product)
                                    <div class="{{ $view === 'grid' ? 'col-6 col-md-4 col-xl-4 col-xxl-3' : 'col-12' }}" 
                                         wire:key="product-{{ $product->id }}">
                                        @if($view === 'grid')
                                            @include('livewire.public.partials.product-card-grid', ['product' => $product])
                                        @else
                                            @include('livewire.public.partials.product-card-list', ['product' => $product])
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Pagination --}}
@if($products->hasPages())
    @include('public.shop.partials.pagination', ['paginator' => $products])
@endif
@else
                            {{-- No Products --}}
                            <div class="no_products_found">
                                <div class="icon">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <h4>No Products Found</h4>
                                <p>Try adjusting your filters or search terms.</p>
                                <button wire:click="clearFilters" class="btn btn-primary">
                                    Clear All Filters
                                </button>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- Mobile Filter Offcanvas 
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileFilterOffcanvas" 
         wire:ignore.self
         aria-labelledby="mobileFilterLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileFilterLabel">
                <i class="fas fa-filter me-2"></i> Filters
                @if($activeFiltersCount > 0)
                    <span class="badge bg-primary ms-2">{{ $activeFiltersCount }}</span>
                @endif
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            @include('livewire.partials.shop-sidebar', ['isMobile' => true])
        </div>
        <div class="offcanvas-footer p-3 border-top bg-light">
            <div class="d-flex gap-2">
                <button wire:click="clearFilters" class="btn btn-outline-secondary flex-fill">
                    Reset
                </button>
                <button type="button" class="btn btn-primary flex-fill" data-bs-dismiss="offcanvas">
                    Show {{ $products->total() }} Results
                </button>
            </div>
        </div>
    </div>--}}

</div>

@push('styles')
<style>
/* Mobile Header */
.mobile_page_header {
    background: var(--primary-color, #ff6b6b);
    padding: 15px 0;
}
.mobile_page_header .page_title {
    color: #fff;
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

/* Category Slider */
.fp_category_slider_section { padding: 10px 0 5px; background: #fff; }
.fp_category_slider {
    display: flex;
    gap: 0;
    overflow-x: auto;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
    padding: 5px 0 8px;
}
.fp_category_slider::-webkit-scrollbar { display: none; }

button.fp_category_item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 0 0 70px;
    width: 70px;
    padding: 0 2px;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: transform 0.15s;
}
button.fp_category_item:active { transform: scale(0.95); }

.fp_cat_icon_box {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
    border-radius: 12px;
    margin-bottom: 6px;
    border: 2px solid transparent;
    color: #555;
    font-size: 20px;
    transition: all 0.15s;
}
.fp_cat_icon_box img {
    width: 32px;
    height: 32px;
    object-fit: contain;
    border-radius: 6px;
}

.fp_category_item.active .fp_cat_icon_box {
    background: var(--primary-color, #ff6b6b);
    border-color: var(--primary-color, #ff6b6b);
    color: #fff;
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
}

.fp_cat_name {
    font-size: 11px;
    font-weight: 500;
    color: #333;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
}
.fp_category_item.active .fp_cat_name {
    color: var(--primary-color, #ff6b6b);
    font-weight: 600;
}

/* Subcategories */
.subcategory_pill {
    padding: 6px 14px;
    background: #f0f0f0;
    border: none;
    border-radius: 20px;
    font-size: 13px;
    color: #555;
    cursor: pointer;
    transition: all 0.2s;
}
.subcategory_pill:hover, .subcategory_pill.active {
    background: var(--primary-color, #ff6b6b);
    color: #fff;
}
.subcategory_pill .count { font-size: 11px; opacity: 0.7; }

/* Loading */
.loading_overlay {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
}

/* No Products */
.no_products_found {
    text-align: center;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 12px;
}
.no_products_found .icon { font-size: 48px; color: #ddd; margin-bottom: 15px; }
.no_products_found h4 { color: #333; margin-bottom: 10px; }
.no_products_found p { color: #666; margin-bottom: 20px; }

/* Pagination */
.pagination_wrapper { display: flex; justify-content: center; }

/* Responsive */
@media (max-width: 359.98px) {
    button.fp_category_item { flex: 0 0 62px; width: 62px; }
    .fp_cat_icon_box { width: 44px; height: 44px; font-size: 18px; }
    .fp_cat_name { font-size: 10px; }
}
@media (min-width: 992px) {
    .fp_category_slider { justify-content: center; }
    button.fp_category_item { flex: 0 0 100px; width: 100px; }
    .fp_cat_icon_box { width: 64px; height: 64px; font-size: 26px; }
    .fp_cat_name { font-size: 13px; }
}
</style>
@endpush

@script
<script>
    // Scroll active category into view on load
    document.addEventListener('livewire:initialized', () => {
        const slider = document.getElementById('categorySlider');
        if (slider) {
            const activeItem = slider.querySelector('.fp_category_item.active');
            if (activeItem) {
                setTimeout(() => {
                    const sliderRect = slider.getBoundingClientRect();
                    const itemRect = activeItem.getBoundingClientRect();
                    const scrollLeft = itemRect.left - sliderRect.left - (sliderRect.width / 2) + (itemRect.width / 2);
                    slider.scrollBy({ left: scrollLeft, behavior: 'smooth' });
                }, 100);
            }
        }
    });
</script>
@endscript
