{{--
    Sidebar Filters Partial
    Displays category tree, price range, and tag filters
--}}

<div class="shop_sidebar">
    
    {{-- Category Filter --}}
    <div class="sidebar_widget category_filter">
        <h4 class="widget_title">Categories</h4>
        <ul class="category_list">
            {{-- All Products Link --}}
            <li class="{{ !isset($currentCategory) ? 'active' : '' }}">
                <a href="{{ route('shop.index') }}">
                    <span class="name">All Products</span>
                    <span class="count">{{ \App\Models\Product::available()->count() }}</span>
                </a>
            </li>
            
            @foreach($categories as $cat)
                <li class="{{ isset($currentCategory) && $currentCategory->id === $cat->id ? 'active' : '' }} {{ $cat->children->count() > 0 ? 'has-children' : '' }}">
                    <a href="{{ route('shop.category', $cat->slug) }}">
                        <span class="name">{{ $cat->name }}</span>
                        <span class="count">{{ $cat->products_count }}</span>
                    </a>
                    
                    {{-- Subcategories --}}
                    @if($cat->children->count() > 0)
                        <ul class="subcategory_list">
                            @foreach($cat->children as $child)
                                <li class="{{ isset($currentCategory) && $currentCategory->id === $child->id ? 'active' : '' }}">
                                    <a href="{{ route('shop.category', $child->slug) }}">
                                        <span class="name">{{ $child->name }}</span>
                                        <span class="count">{{ $child->products_count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Price Range Filter --}}
    <div class="sidebar_widget price_filter">
        <h4 class="widget_title">Price Range</h4>
        <form action="{{ isset($currentCategory) ? route('shop.category', $currentCategory->slug) : route('shop.index') }}" method="GET" id="priceFilterForm">
            {{-- Preserve existing filters --}}
            @if(!empty($filters['sort']))
                <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
            @endif
            @if(!empty($filters['search']))
                <input type="hidden" name="q" value="{{ $filters['search'] }}">
            @endif
            
            <div class="price_range_wrapper">
                <div class="price_inputs">
                    <div class="price_input">
                        <label for="min_price">Min</label>
                        <input type="number" 
                               name="min_price" 
                               id="min_price"
                               value="{{ $filters['min_price'] ?? $priceRange['min'] }}"
                               min="{{ $priceRange['min'] }}"
                               max="{{ $priceRange['max'] }}"
                               placeholder="Rp {{ number_format($priceRange['min'], 0, ',', '.') }}">
                    </div>
                    <span class="separator">-</span>
                    <div class="price_input">
                        <label for="max_price">Max</label>
                        <input type="number" 
                               name="max_price" 
                               id="max_price"
                               value="{{ $filters['max_price'] ?? $priceRange['max'] }}"
                               min="{{ $priceRange['min'] }}"
                               max="{{ $priceRange['max'] }}"
                               placeholder="Rp {{ number_format($priceRange['max'], 0, ',', '.') }}">
                    </div>
                </div>
                
                {{-- Price Range Slider (optional - requires noUiSlider) --}}
                <div id="priceSlider" class="price_slider mt-3"></div>
                
                <button type="submit" class="filter_btn common_btn mt-3 w-100">
                    Apply Filter
                </button>
            </div>
        </form>
        
        {{-- Quick Price Filters --}}
        <div class="quick_price_filters mt-3">
            <a href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => 50000]) }}" 
               class="quick_filter {{ $filters['max_price'] == 50000 ? 'active' : '' }}">
                Under Rp 50K
            </a>
            <a href="{{ request()->fullUrlWithQuery(['min_price' => 50000, 'max_price' => 100000]) }}" 
               class="quick_filter {{ $filters['min_price'] == 50000 && $filters['max_price'] == 100000 ? 'active' : '' }}">
                Rp 50K - 100K
            </a>
            <a href="{{ request()->fullUrlWithQuery(['min_price' => 100000, 'max_price' => 500000]) }}" 
               class="quick_filter {{ $filters['min_price'] == 100000 && $filters['max_price'] == 500000 ? 'active' : '' }}">
                Rp 100K - 500K
            </a>
            <a href="{{ request()->fullUrlWithQuery(['min_price' => 500000, 'max_price' => null]) }}" 
               class="quick_filter {{ $filters['min_price'] == 500000 && empty($filters['max_price']) ? 'active' : '' }}">
                Over Rp 500K
            </a>
        </div>
    </div>

    {{-- Tags Filter --}}
    @if(count($availableTags) > 0)
    <div class="sidebar_widget tags_filter">
        <h4 class="widget_title">Tags</h4>
        <div class="tags_list">
            @foreach($availableTags as $tag)
                @php
                    $isActive = is_array($filters['tags']) && in_array($tag, $filters['tags']);
                    $newTags = $isActive 
                        ? array_diff($filters['tags'], [$tag])
                        : array_merge($filters['tags'] ?? [], [$tag]);
                @endphp
                <a href="{{ request()->fullUrlWithQuery(['tags' => $newTags]) }}" 
                   class="tag_item {{ $isActive ? 'active' : '' }}">
                    {{ ucfirst($tag) }}
                    @if($isActive)
                        <i class="fas fa-times"></i>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Featured Products Toggle --}}
    <div class="sidebar_widget featured_filter">
        <div class="form-check form-switch">
            <input class="form-check-input" 
                   type="checkbox" 
                   id="featuredToggle"
                   {{ $filters['featured'] ? 'checked' : '' }}
                   onchange="window.location.href='{{ request()->fullUrlWithQuery(['featured' => !$filters['featured'] ? '1' : null]) }}'">
            <label class="form-check-label" for="featuredToggle">
                Show Featured Only
            </label>
        </div>
    </div>

    {{-- Clear All Filters --}}
    @if(!empty($filters['category']) || !empty($filters['min_price']) || !empty($filters['max_price']) || !empty($filters['tags']) || $filters['featured'])
    <div class="sidebar_widget clear_filters">
        <a href="{{ route('shop.index') }}" class="clear_btn">
            <i class="fas fa-times-circle"></i> Clear All Filters
        </a>
    </div>
    @endif

</div>

@push('styles')
<style>
.shop_sidebar {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.sidebar_widget {
    margin-bottom: 25px;
    padding-bottom: 25px;
    border-bottom: 1px solid #e9ecef;
}

.sidebar_widget:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.widget_title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

/* Category List */
.category_list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.category_list > li {
    margin-bottom: 8px;
}

.category_list > li > a {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #fff;
    border-radius: 4px;
    color: #555;
    text-decoration: none;
    transition: all 0.2s;
}

.category_list > li > a:hover,
.category_list > li.active > a {
    background: var(--primary-color, #ff6b6b);
    color: #fff;
}

.category_list .count {
    font-size: 12px;
    background: rgba(0,0,0,0.1);
    padding: 2px 8px;
    border-radius: 10px;
}

.subcategory_list {
    list-style: none;
    padding: 5px 0 5px 20px;
    margin: 0;
}

.subcategory_list li a {
    display: flex;
    justify-content: space-between;
    padding: 5px 10px;
    font-size: 14px;
    color: #666;
    text-decoration: none;
}

.subcategory_list li.active a,
.subcategory_list li a:hover {
    color: var(--primary-color, #ff6b6b);
}

/* Price Filter */
.price_inputs {
    display: flex;
    align-items: center;
    gap: 10px;
}

.price_input {
    flex: 1;
}

.price_input label {
    display: block;
    font-size: 12px;
    color: #888;
    margin-bottom: 4px;
}

.price_input input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.separator {
    color: #999;
}

.quick_price_filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.quick_filter {
    padding: 5px 10px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 12px;
    color: #666;
    text-decoration: none;
    transition: all 0.2s;
}

.quick_filter:hover,
.quick_filter.active {
    background: var(--primary-color, #ff6b6b);
    border-color: var(--primary-color, #ff6b6b);
    color: #fff;
}

/* Tags */
.tags_list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.tag_item {
    padding: 5px 12px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 20px;
    font-size: 13px;
    color: #666;
    text-decoration: none;
    transition: all 0.2s;
}

.tag_item:hover,
.tag_item.active {
    background: var(--primary-color, #ff6b6b);
    border-color: var(--primary-color, #ff6b6b);
    color: #fff;
}

.tag_item i {
    margin-left: 5px;
    font-size: 10px;
}

/* Clear Filters */
.clear_btn {
    display: block;
    text-align: center;
    padding: 10px;
    background: #dc3545;
    color: #fff;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
}

.clear_btn:hover {
    background: #c82333;
    color: #fff;
}

/* Mobile Sidebar */
@media (max-width: 991px) {
    .shop_sidebar {
        margin-bottom: 30px;
    }
}
</style>
@endpush
