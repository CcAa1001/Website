{{-- Shop Sidebar (Livewire) --}}

@php $isMobile = $isMobile ?? false; @endphp

<div class="sidebar_filters">
    
    {{-- Categories --}}
    <div class="filter_section">
        <h6 class="filter_title">Categories</h6>
        <ul class="category_list">
            <li class="{{ !$selectedCategory ? 'active' : '' }}">
                <button type="button" wire:click="selectCategory(null)" class="category_link">
                    <span>All Products</span>
                    <span class="count">{{ \App\Models\Product::available()->count() }}</span>
                </button>
            </li>
            @foreach($categories as $cat)
                <li class="{{ $selectedCategory === $cat->slug ? 'active' : '' }}">
                    <button type="button" wire:click="selectCategory('{{ $cat->slug }}')" class="category_link">
                        <span>{{ $cat->name }}</span>
                        <span class="count">{{ $cat->products_count }}</span>
                    </button>
                    
                    @if($cat->children->count() > 0)
                        <ul class="subcategory_list">
                            @foreach($cat->children as $child)
                                <li class="{{ $selectedCategory === $child->slug ? 'active' : '' }}">
                                    <button type="button" wire:click="selectCategory('{{ $child->slug }}')" class="category_link">
                                        <span>{{ $child->name }}</span>
                                        <span class="count">{{ $child->products_count }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Price Range --}}
    <div class="filter_section">
        <h6 class="filter_title">Price Range</h6>
        <div class="price_inputs">
            <div class="input_group">
                <label>Min</label>
                <input type="number" 
                       wire:model.live.debounce.500ms="minPrice"
                       placeholder="Rp {{ number_format($priceRange['min'], 0, ',', '.') }}"
                       min="{{ $priceRange['min'] }}"
                       max="{{ $priceRange['max'] }}">
            </div>
            <span class="separator">-</span>
            <div class="input_group">
                <label>Max</label>
                <input type="number" 
                       wire:model.live.debounce.500ms="maxPrice"
                       placeholder="Rp {{ number_format($priceRange['max'], 0, ',', '.') }}"
                       min="{{ $priceRange['min'] }}"
                       max="{{ $priceRange['max'] }}">
            </div>
        </div>
        
        {{-- Quick Price Buttons --}}
        <div class="quick_prices">
            <button type="button" 
                    wire:click="$set('minPrice', null); $set('maxPrice', 50000)"
                    class="quick_btn {{ $maxPrice == 50000 && !$minPrice ? 'active' : '' }}">
                &lt; 50K
            </button>
            <button type="button" 
                    wire:click="$set('minPrice', 50000); $set('maxPrice', 100000)"
                    class="quick_btn {{ $minPrice == 50000 && $maxPrice == 100000 ? 'active' : '' }}">
                50-100K
            </button>
            <button type="button" 
                    wire:click="$set('minPrice', 100000); $set('maxPrice', null)"
                    class="quick_btn {{ $minPrice == 100000 && !$maxPrice ? 'active' : '' }}">
                &gt; 100K
            </button>
        </div>
    </div>

    {{-- Tags --}}
    @if(count($availableTags) > 0)
    <div class="filter_section">
        <h6 class="filter_title">Tags</h6>
        <div class="tags_list">
            @foreach($availableTags as $tag)
                <button type="button" 
                        wire:click="toggleTag('{{ $tag }}')"
                        class="tag_btn {{ in_array($tag, $selectedTags) ? 'active' : '' }}">
                    {{ ucfirst($tag) }}
                    @if(in_array($tag, $selectedTags))
                        <i class="fas fa-check"></i>
                    @endif
                </button>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Featured Toggle --}}
    <div class="filter_section">
        <label class="toggle_label">
            <input type="checkbox" 
                   wire:model.live="featuredOnly"
                   class="toggle_input">
            <span class="toggle_text">Featured Only</span>
        </label>
    </div>

    {{-- Clear Filters --}}
    @if($activeFiltersCount > 0)
    <div class="filter_section">
        <button type="button" wire:click="clearFilters" class="clear_filters_btn">
            <i class="fas fa-times-circle"></i> Clear All Filters
        </button>
    </div>
    @endif

</div>

<style>
.sidebar_filters {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
}

.filter_section {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}
.filter_section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.filter_title {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 12px;
}

.category_list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.category_list > li { margin-bottom: 4px; }

.category_link {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 8px 12px;
    background: #fff;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    color: #555;
    cursor: pointer;
    text-align: left;
    transition: all 0.2s;
}
.category_link:hover,
.category_list li.active > .category_link {
    background: var(--primary-color, #ff6b6b);
    color: #fff;
}

.category_link .count {
    font-size: 11px;
    background: rgba(0,0,0,0.1);
    padding: 2px 6px;
    border-radius: 10px;
}

.subcategory_list {
    list-style: none;
    padding: 5px 0 5px 15px;
    margin: 0;
}
.subcategory_list .category_link {
    padding: 6px 10px;
    font-size: 12px;
    background: transparent;
}
.subcategory_list li.active .category_link {
    color: var(--primary-color, #ff6b6b);
    font-weight: 600;
    background: transparent;
}

.price_inputs {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    margin-bottom: 10px;
}
.price_inputs .input_group { flex: 1; }
.price_inputs label {
    display: block;
    font-size: 11px;
    color: #888;
    margin-bottom: 4px;
}
.price_inputs input {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 13px;
}
.price_inputs input:focus {
    outline: none;
    border-color: var(--primary-color, #ff6b6b);
}
.price_inputs .separator { color: #999; padding-bottom: 8px; }

.quick_prices { display: flex; gap: 6px; }
.quick_btn {
    flex: 1;
    padding: 6px 8px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: #fff;
    font-size: 11px;
    color: #666;
    cursor: pointer;
}
.quick_btn:hover, .quick_btn.active {
    background: var(--primary-color, #ff6b6b);
    border-color: var(--primary-color, #ff6b6b);
    color: #fff;
}

.tags_list { display: flex; flex-wrap: wrap; gap: 6px; }
.tag_btn {
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 15px;
    background: #fff;
    font-size: 12px;
    color: #666;
    cursor: pointer;
}
.tag_btn:hover, .tag_btn.active {
    background: var(--primary-color, #ff6b6b);
    border-color: var(--primary-color, #ff6b6b);
    color: #fff;
}
.tag_btn i { margin-left: 4px; font-size: 10px; }

.toggle_label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}
.toggle_input {
    width: 18px;
    height: 18px;
    accent-color: var(--primary-color, #ff6b6b);
}
.toggle_text { font-size: 13px; color: #555; }

.clear_filters_btn {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 6px;
    background: #dc3545;
    color: #fff;
    font-size: 13px;
    cursor: pointer;
}
.clear_filters_btn:hover { background: #c82333; }
</style>
