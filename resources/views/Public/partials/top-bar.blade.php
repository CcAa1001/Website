{{--
    Top Bar Partial (Mobile-First Design)
    Displays search, product count, sorting dropdown, and view toggle (grid/list)
--}}

<div class="shop_top_bar">
    
    {{-- Mobile Search Bar (Always visible on mobile) --}}
    <div class="shop_search_wrapper d-lg-none mb-3">
        <form action="{{ route('shop.search') }}" method="GET" class="shop_search_form">
            {{-- Preserve category if in category page --}}
            @if(isset($category))
                <input type="hidden" name="category" value="{{ $category->slug }}">
            @endif
            
            <div class="search_input_group">
                <input type="text" 
                       name="q" 
                       class="form-control" 
                       placeholder="Search products..."
                       value="{{ $filters['search'] ?? request('q') }}"
                       autocomplete="off">
                <button type="submit" class="search_btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- Desktop Search + Results Row --}}
    <div class="row align-items-center mb-lg-0 mb-2">
        
        {{-- Results Count (Hidden on small mobile) --}}
        <div class="col-lg-3 col-md-4 d-none d-md-block">
            <div class="showing_result">
                <p>
                    Showing 
                    <strong>{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</strong> 
                    of <strong>{{ $products->total() }}</strong> products
                </p>
            </div>
        </div>

        {{-- Desktop Search (Hidden on mobile) --}}
        <div class="col-lg-5 col-md-4 d-none d-lg-block">
            <form action="{{ route('shop.search') }}" method="GET" class="shop_search_form desktop_search">
                @if(isset($category))
                    <input type="hidden" name="category" value="{{ $category->slug }}">
                @endif
                
                <div class="search_input_group">
                    <input type="text" 
                           name="q" 
                           class="form-control" 
                           placeholder="Search in {{ isset($category) ? $category->name : 'all products' }}..."
                           value="{{ $filters['search'] ?? request('q') }}">
                    <button type="submit" class="search_btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        {{-- Sorting & View Toggle --}}
        <div class="col-lg-4 col-md-4 col-12">
            <div class="top_bar_right d-flex justify-content-between justify-content-md-end align-items-center">
                
                {{-- Mobile Results Count --}}
                <div class="mobile_results d-md-none">
                    <span class="results_text">{{ $products->total() }} items</span>
                </div>
                
                {{-- Sort & Filter Controls --}}
                <div class="controls_wrapper d-flex align-items-center">
                    
                    {{-- Mobile Filter Button --}}
                    <button class="filter_toggle_btn d-lg-none me-2" 
                            type="button" 
                            data-bs-toggle="offcanvas" 
                            data-bs-target="#filterOffcanvas"
                            aria-controls="filterOffcanvas">
                        <i class="fas fa-sliders-h"></i>
                        <span class="d-none d-sm-inline">Filter</span>
                        @php
                            $activeFilters = collect($filters)->filter(function($value, $key) {
                                return !empty($value) && $key !== 'sort' && $value !== 'default';
                            })->count();
                        @endphp
                        @if($activeFilters > 0)
                            <span class="filter_badge">{{ $activeFilters }}</span>
                        @endif
                    </button>

                    {{-- Sort Dropdown --}}
                    <div class="sort_dropdown">
                        <form id="sortForm" class="d-flex align-items-center">
                            {{-- Preserve other query parameters --}}
                            @foreach(request()->except(['sort', 'page']) as $key => $value)
                                @if(is_array($value))
                                    @foreach($value as $v)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            
                            <label for="sortSelect" class="me-2 d-none d-xl-inline text-nowrap">Sort:</label>
                            <select name="sort" 
                                    id="sortSelect" 
                                    class="form-select form-select-sm"
                                    onchange="this.form.submit()">
                                <option value="default" {{ ($filters['sort'] ?? 'default') === 'default' ? 'selected' : '' }}>
                                    Default
                                </option>
                                <option value="newest" {{ ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' }}>
                                    Newest
                                </option>
                                <option value="price_low" {{ ($filters['sort'] ?? '') === 'price_low' ? 'selected' : '' }}>
                                    Price ↑
                                </option>
                                <option value="price_high" {{ ($filters['sort'] ?? '') === 'price_high' ? 'selected' : '' }}>
                                    Price ↓
                                </option>
                                <option value="name_asc" {{ ($filters['sort'] ?? '') === 'name_asc' ? 'selected' : '' }}>
                                    A - Z
                                </option>
                                <option value="name_desc" {{ ($filters['sort'] ?? '') === 'name_desc' ? 'selected' : '' }}>
                                    Z - A
                                </option>
                                <option value="featured" {{ ($filters['sort'] ?? '') === 'featured' ? 'selected' : '' }}>
                                    Featured
                                </option>
                            </select>
                        </form>
                    </div>

                    {{-- View Toggle --}}
                    <nav class="view_toggle ms-2 d-none d-sm-block">
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            {{-- Grid View --}}
                            <button class="nav-link active" 
                                    id="nav-home-tab" 
                                    data-bs-toggle="tab"
                                    data-bs-target="#nav-home" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="nav-home"
                                    aria-selected="true"
                                    title="Grid View">
                                <i class="fas fa-th"></i>
                            </button>
                            
                            {{-- List View --}}
                            <button class="nav-link" 
                                    id="nav-profile-tab" 
                                    data-bs-toggle="tab"
                                    data-bs-target="#nav-profile" 
                                    type="button" 
                                    role="tab" 
                                    aria-controls="nav-profile"
                                    aria-selected="false"
                                    title="List View">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </nav>

                </div>
            </div>
        </div>

    </div>

    {{-- Active Filters Pills (Mobile & Desktop) --}}
    @if(!empty($filters['search']) || !empty($filters['min_price']) || !empty($filters['max_price']) || !empty($filters['tags']) || !empty($filters['featured']))
    <div class="active_filters_bar mt-2">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="filter_label">Active:</span>
            
            {{-- Search Term --}}
            @if(!empty($filters['search']))
                <a href="{{ request()->fullUrlWithQuery(['q' => null]) }}" class="filter_pill">
                    <i class="fas fa-search"></i> "{{ Str::limit($filters['search'], 15) }}"
                    <i class="fas fa-times remove"></i>
                </a>
            @endif
            
            {{-- Price Range --}}
            @if(!empty($filters['min_price']) || !empty($filters['max_price']))
                <a href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => null]) }}" class="filter_pill">
                    <i class="fas fa-tag"></i> 
                    @if(!empty($filters['min_price']) && !empty($filters['max_price']))
                        Rp {{ number_format($filters['min_price'], 0, ',', '.') }} - {{ number_format($filters['max_price'], 0, ',', '.') }}
                    @elseif(!empty($filters['min_price']))
                        Min Rp {{ number_format($filters['min_price'], 0, ',', '.') }}
                    @else
                        Max Rp {{ number_format($filters['max_price'], 0, ',', '.') }}
                    @endif
                    <i class="fas fa-times remove"></i>
                </a>
            @endif
            
            {{-- Tags --}}
            @if(!empty($filters['tags']) && is_array($filters['tags']))
                @foreach($filters['tags'] as $tag)
                    @php
                        $newTags = array_diff($filters['tags'], [$tag]);
                    @endphp
                    <a href="{{ request()->fullUrlWithQuery(['tags' => $newTags ?: null]) }}" class="filter_pill">
                        <i class="fas fa-hashtag"></i> {{ $tag }}
                        <i class="fas fa-times remove"></i>
                    </a>
                @endforeach
            @endif
            
            {{-- Featured --}}
            @if(!empty($filters['featured']))
                <a href="{{ request()->fullUrlWithQuery(['featured' => null]) }}" class="filter_pill">
                    <i class="fas fa-star"></i> Featured
                    <i class="fas fa-times remove"></i>
                </a>
            @endif
            
            {{-- Clear All --}}
            <a href="{{ isset($category) ? route('shop.category', $category->slug) : route('shop.index') }}" class="clear_all_btn">
                Clear All
            </a>
        </div>
    </div>
    @endif

</div>

{{-- Mobile Filter Offcanvas --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">
            <i class="fas fa-filter me-2"></i> Filters
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        {{-- Include the sidebar filters content here for mobile --}}
        {{-- IMPORTANT: Pass all required variables explicitly --}}
        @include('shop.partials.sidebar-filters', [
            'categories' => $categories ?? collect(),
            'filters' => $filters ?? [],
            'priceRange' => $priceRange ?? ['min' => 0, 'max' => 1000000],
            'availableTags' => $availableTags ?? [],
            'currentCategory' => $category ?? null,
            'isMobile' => true,
        ])
    </div>
    <div class="offcanvas-footer p-3 border-top">
        <div class="d-flex gap-2">
            <a href="{{ isset($category) ? route('shop.category', $category->slug) : route('shop.index') }}" 
               class="btn btn-outline-secondary flex-fill">
                Reset
            </a>
            <button type="button" class="btn btn-primary flex-fill" data-bs-dismiss="offcanvas">
                Show {{ $products->total() }} Results
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ===========================
   TOP BAR - MOBILE FIRST
   =========================== */

.shop_top_bar {
    background: #fff;
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

/* Search Bar */
.shop_search_form {
    width: 100%;
}

.search_input_group {
    position: relative;
    display: flex;
    align-items: center;
}

.search_input_group input {
    width: 100%;
    padding: 10px 45px 10px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 25px;
    font-size: 14px;
    background: #f8f9fa;
    transition: all 0.2s;
}

.search_input_group input:focus {
    outline: none;
    border-color: var(--primary-color, #ff6b6b);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
}

.search_input_group .search_btn {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    width: 35px;
    height: 35px;
    border: none;
    background: var(--primary-color, #ff6b6b);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}

.search_input_group .search_btn:hover {
    background: #e55a5a;
}

/* Results Count */
.showing_result p {
    margin: 0;
    font-size: 13px;
    color: #666;
}

.mobile_results {
    font-size: 13px;
    color: #666;
}

.mobile_results .results_text {
    font-weight: 500;
}

/* Filter Toggle Button (Mobile) */
.filter_toggle_btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
    color: #333;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    position: relative;
    transition: all 0.2s;
}

.filter_toggle_btn:hover,
.filter_toggle_btn:focus {
    border-color: var(--primary-color, #ff6b6b);
    color: var(--primary-color, #ff6b6b);
}

.filter_badge {
    position: absolute;
    top: -6px;
    right: -6px;
    min-width: 18px;
    height: 18px;
    background: var(--primary-color, #ff6b6b);
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Sort Dropdown */
.sort_dropdown select {
    padding: 8px 30px 8px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 13px;
    background-color: #fff;
    cursor: pointer;
    min-width: 100px;
}

.sort_dropdown select:focus {
    outline: none;
    border-color: var(--primary-color, #ff6b6b);
}

/* View Toggle */
.view_toggle .nav-tabs {
    border: none;
    gap: 4px;
}

.view_toggle .nav-link {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 8px 10px;
    color: #666;
    background: #fff;
    font-size: 14px;
}

.view_toggle .nav-link:hover,
.view_toggle .nav-link.active {
    background: var(--primary-color, #ff6b6b);
    border-color: var(--primary-color, #ff6b6b);
    color: #fff;
}

/* Active Filters Bar */
.active_filters_bar {
    padding-top: 10px;
    border-top: 1px solid #eee;
}

.filter_label {
    font-size: 12px;
    color: #888;
    font-weight: 500;
}

.filter_pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    background: #f0f0f0;
    border-radius: 20px;
    font-size: 12px;
    color: #555;
    text-decoration: none;
    transition: all 0.2s;
}

.filter_pill:hover {
    background: #e0e0e0;
    color: #333;
}

.filter_pill .remove {
    font-size: 10px;
    opacity: 0.6;
    margin-left: 2px;
}

.filter_pill:hover .remove {
    opacity: 1;
    color: #dc3545;
}

.clear_all_btn {
    font-size: 12px;
    color: #dc3545;
    text-decoration: none;
    font-weight: 500;
}

.clear_all_btn:hover {
    text-decoration: underline;
}

/* Offcanvas Filter (Mobile) */
#filterOffcanvas {
    width: 300px;
}

#filterOffcanvas .offcanvas-header {
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
}

#filterOffcanvas .offcanvas-body {
    padding: 15px;
}

#filterOffcanvas .offcanvas-footer {
    background: #f8f9fa;
}

/* ===========================
   RESPONSIVE STYLES
   =========================== */

/* Extra Small (< 576px) */
@media (max-width: 575.98px) {
    .shop_top_bar {
        padding: 10px 12px;
    }
    
    .sort_dropdown select {
        min-width: 90px;
        padding: 8px 25px 8px 10px;
        font-size: 12px;
    }
    
    .filter_toggle_btn {
        padding: 8px 10px;
    }
    
    .controls_wrapper {
        gap: 8px;
    }
    
    .active_filters_bar {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 5px;
        margin: 0 -12px;
        padding-left: 12px;
        padding-right: 12px;
    }
    
    .active_filters_bar::-webkit-scrollbar {
        display: none;
    }
}

/* Small (576px - 767px) */
@media (min-width: 576px) and (max-width: 767.98px) {
    .shop_top_bar {
        padding: 12px 15px;
    }
}

/* Medium (768px - 991px) */
@media (min-width: 768px) and (max-width: 991.98px) {
    .shop_top_bar {
        padding: 15px;
    }
    
    .sort_dropdown select {
        min-width: 130px;
    }
}

/* Large (992px+) - Desktop */
@media (min-width: 992px) {
    .shop_top_bar {
        padding: 15px 20px;
    }
    
    .desktop_search {
        max-width: 350px;
    }
    
    .desktop_search input {
        padding: 12px 50px 12px 18px;
    }
    
    .desktop_search .search_btn {
        width: 38px;
        height: 38px;
        right: 6px;
    }
    
    .sort_dropdown select {
        min-width: 150px;
        padding: 10px 35px 10px 15px;
        font-size: 14px;
    }
    
    .view_toggle .nav-link {
        padding: 10px 12px;
    }
}

/* Extra Large (1200px+) */
@media (min-width: 1200px) {
    .desktop_search {
        max-width: 400px;
    }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
    .filter_toggle_btn,
    .sort_dropdown select,
    .view_toggle .nav-link,
    .search_btn {
        min-height: 44px; /* Apple's recommended touch target */
    }
    
    .filter_pill {
        padding: 6px 12px;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle persistence
    const viewTabs = document.querySelectorAll('[data-bs-toggle="tab"]');
    viewTabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            localStorage.setItem('shopView', e.target.id);
        });
    });
    
    // Restore view preference
    const savedView = localStorage.getItem('shopView');
    if (savedView) {
        const tab = document.getElementById(savedView);
        if (tab) {
            new bootstrap.Tab(tab).show();
        }
    }
    
    // Search input auto-focus on mobile when search icon clicked
    const mobileSearchInput = document.querySelector('.shop_search_wrapper input');
    if (mobileSearchInput) {
        // Clear search on X button (if input has value)
        mobileSearchInput.addEventListener('input', function() {
            // Could add clear button functionality here
        });
    }
    
    // Close offcanvas when clicking apply filter (handled by data-bs-dismiss)
    
    // Scroll active filters into view on mobile
    const activeFiltersBar = document.querySelector('.active_filters_bar');
    if (activeFiltersBar && window.innerWidth < 576) {
        const firstPill = activeFiltersBar.querySelector('.filter_pill');
        if (firstPill) {
            // Scroll to show active filters
            activeFiltersBar.scrollLeft = 0;
        }
    }
});
</script>
@endpush