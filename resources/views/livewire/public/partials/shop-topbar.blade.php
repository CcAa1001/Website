{{-- Shop Top Bar (Livewire) --}}

<div class="shop_top_bar mb-3">
    
    {{-- Mobile Search --}}
    <div class="d-lg-none mb-3">
        <div class="search_input_group">
            <input type="text" 
                   wire:model.live.debounce.300ms="search"
                   class="form-control" 
                   placeholder="Search products...">
            <button type="button" class="search_btn">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>

    <div class="row align-items-center">
        
        {{-- Results Count --}}
        <div class="col-lg-3 col-6">
            <div class="results_count">
                <span class="d-none d-md-inline">Showing </span>
                <strong>{{ $products->total() }}</strong> 
                <span class="d-none d-sm-inline">products</span>
                <span class="d-sm-none">items</span>
                @if($currentCategory)
                    <span class="d-none d-md-inline text-muted">in {{ $currentCategory->name }}</span>
                @endif
            </div>
        </div>

        {{-- Desktop Search --}}
        <div class="col-lg-4 d-none d-lg-block">
            <div class="search_input_group">
                <input type="text" 
                       wire:model.live.debounce.300ms="search"
                       class="form-control" 
                       placeholder="Search products...">
                <button type="button" class="search_btn">
                    <i class="fas fa-search"></i>
                </button>
                @if($search)
                    <button type="button" 
                            wire:click="$set('search', '')"
                            class="clear_search_btn">
                        <i class="fas fa-times"></i>
                    </button>
                @endif
            </div>
        </div>

        {{-- Sort & View --}}
        <div class="col-lg-5 col-6">
            <div class="d-flex justify-content-end align-items-center gap-2">
                
                {{-- Mobile Filter Button 
                <button class="filter_btn d-lg-none" 
                        type="button"
                        data-bs-toggle="offcanvas" 
                        data-bs-target="#mobileFilterOffcanvas">
                    <i class="fas fa-sliders-h"></i>
                    @if($activeFiltersCount > 0)
                        <span class="badge">{{ $activeFiltersCount }}</span>
                    @endif
                </button>--}}

                {{-- Sort Dropdown 
                <select wire:model.live="sortBy" class="form-select form-select-sm sort_select">
                    <option value="default">Default</option>
                    <option value="newest">Newest</option>
                    <option value="price_low">Price: Low-High</option>
                    <option value="price_high">Price: High-Low</option>
                    <option value="name_asc">Name: A-Z</option>
                    <option value="name_desc">Name: Z-A</option>
                </select>--}}

                {{-- View Toggle (Desktop) --}}
                <div class="view_toggle d-none d-md-flex">
                    <button type="button" 
                            wire:click="setView('grid')"
                            class="view_btn {{ $view === 'grid' ? 'active' : '' }}">
                        <i class="fas fa-th"></i>
                    </button>
                    <button type="button" 
                            wire:click="setView('list')"
                            class="view_btn {{ $view === 'list' ? 'active' : '' }}">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Filters Pills --}}
    @if($activeFiltersCount > 0)
        <div class="active_filters mt-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="filter_label">Filters:</span>
                
                @if($selectedCategory && $currentCategory)
                    <span class="filter_pill">
                        <i class="fas fa-folder"></i> {{ $currentCategory->name }}
                        <button type="button" wire:click="clearCategory" class="remove_btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                @endif

                @if($search)
                    <span class="filter_pill">
                        <i class="fas fa-search"></i> "{{ Str::limit($search, 15) }}"
                        <button type="button" wire:click="$set('search', '')" class="remove_btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                @endif

                @if($minPrice || $maxPrice)
                    <span class="filter_pill">
                        <i class="fas fa-tag"></i> 
                        @if($minPrice && $maxPrice)
                            Rp {{ number_format($minPrice, 0, ',', '.') }} - {{ number_format($maxPrice, 0, ',', '.') }}
                        @elseif($minPrice)
                            Min Rp {{ number_format($minPrice, 0, ',', '.') }}
                        @else
                            Max Rp {{ number_format($maxPrice, 0, ',', '.') }}
                        @endif
                        <button type="button" wire:click="$set('minPrice', null); $set('maxPrice', null)" class="remove_btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                @endif

                @foreach($selectedTags as $tag)
                    <span class="filter_pill">
                        <i class="fas fa-hashtag"></i> {{ $tag }}
                        <button type="button" wire:click="toggleTag('{{ $tag }}')" class="remove_btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                @endforeach

                @if($featuredOnly)
                    <span class="filter_pill">
                        <i class="fas fa-star"></i> Featured
                        <button type="button" wire:click="$set('featuredOnly', false)" class="remove_btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                @endif

                <button type="button" wire:click="clearFilters" class="clear_all_btn">
                    Clear All
                </button>
            </div>
        </div>
    @endif
</div>

<style>
.shop_top_bar {
    background: #fff;
    padding: 12px 15px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.results_count { font-size: 14px; color: #555; }

.search_input_group { position: relative; }
.search_input_group input {
    width: 100%;
    padding: 10px 80px 10px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 25px;
    font-size: 14px;
}
.search_input_group input:focus {
    outline: none;
    border-color: var(--primary-color, #ff6b6b);
}
.search_input_group .search_btn {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    width: 34px;
    height: 34px;
    border: none;
    background: var(--primary-color, #ff6b6b);
    color: #fff;
    border-radius: 50%;
    cursor: pointer;
}
.search_input_group .clear_search_btn {
    position: absolute;
    right: 45px;
    top: 50%;
    transform: translateY(-50%);
    width: 24px;
    height: 24px;
    border: none;
    background: #ddd;
    color: #666;
    border-radius: 50%;
    cursor: pointer;
    font-size: 10px;
}

.filter_btn {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
    font-size: 14px;
    cursor: pointer;
    position: relative;
}
.filter_btn .badge {
    position: absolute;
    top: -5px;
    right: -5px;
    min-width: 18px;
    height: 18px;
    background: var(--primary-color, #ff6b6b);
    color: #fff;
    font-size: 10px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sort_select {
    min-width: 120px;
    padding: 8px 30px 8px 12px;
    border-radius: 8px;
    font-size: 13px;
}

.view_toggle { display: flex; gap: 4px; }
.view_btn {
    width: 36px;
    height: 36px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.view_btn.active, .view_btn:hover {
    background: var(--primary-color, #ff6b6b);
    border-color: var(--primary-color, #ff6b6b);
    color: #fff;
}

.active_filters { padding-top: 12px; border-top: 1px solid #eee; }
.filter_label { font-size: 12px; color: #888; }
.filter_pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px 4px 10px;
    background: #f0f0f0;
    border-radius: 20px;
    font-size: 12px;
    color: #555;
}
.filter_pill .remove_btn {
    width: 18px;
    height: 18px;
    border: none;
    background: transparent;
    color: #999;
    cursor: pointer;
    padding: 0;
    font-size: 10px;
}
.filter_pill .remove_btn:hover { color: #dc3545; }
.clear_all_btn {
    border: none;
    background: transparent;
    color: #dc3545;
    font-size: 12px;
    cursor: pointer;
}
.clear_all_btn:hover { text-decoration: underline; }

@media (max-width: 575px) {
    .shop_top_bar { padding: 10px 12px; }
    .sort_select { min-width: 100px; font-size: 12px; }
}
</style>
