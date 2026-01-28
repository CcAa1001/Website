{{--
    No Products Found Partial
    Displayed when no products match the filters/search
--}}

<div class="no_products_found">
    <div class="icon">
        <i class="far fa-box-open"></i>
    </div>
    <h3>No Products Found</h3>
    <p>
        @if(isset($query) && !empty($query))
            We couldn't find any products matching "<strong>{{ $query }}</strong>".
        @elseif(isset($category))
            No products available in the "{{ $category->name }}" category yet.
        @else
            No products match your current filters.
        @endif
    </p>
    
    <div class="suggestions">
        <p>Try the following:</p>
        <ul>
            <li>Check your spelling</li>
            <li>Use more general search terms</li>
            <li>Remove some filters</li>
            <li>Browse our categories</li>
        </ul>
    </div>

    <div class="actions">
        <a href="{{ route('shop.index') }}" class="common_btn">
            <i class="fas fa-arrow-left"></i> Browse All Products
        </a>
        @if(isset($category))
            <a href="{{ route('shop.index') }}" class="common_btn outline">
                Clear Category Filter
            </a>
        @endif
    </div>
</div>

@pushOnce('styles')
<style>
.no_products_found {
    text-align: center;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 12px;
    margin: 20px 0;
}

.no_products_found .icon {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

.no_products_found h3 {
    font-size: 24px;
    color: #333;
    margin-bottom: 10px;
}

.no_products_found > p {
    font-size: 16px;
    color: #666;
    margin-bottom: 25px;
}

.no_products_found .suggestions {
    text-align: left;
    display: inline-block;
    background: #fff;
    padding: 20px 30px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.no_products_found .suggestions p {
    font-weight: 600;
    margin-bottom: 10px;
    color: #555;
}

.no_products_found .suggestions ul {
    margin: 0;
    padding-left: 20px;
    color: #777;
}

.no_products_found .suggestions li {
    margin-bottom: 5px;
}

.no_products_found .actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.no_products_found .common_btn {
    padding: 12px 25px;
    background: var(--primary-color, #ff6b6b);
    color: #fff;
    border: 2px solid var(--primary-color, #ff6b6b);
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
}

.no_products_found .common_btn:hover {
    background: #e55656;
    border-color: #e55656;
}

.no_products_found .common_btn.outline {
    background: transparent;
    color: var(--primary-color, #ff6b6b);
}

.no_products_found .common_btn.outline:hover {
    background: var(--primary-color, #ff6b6b);
    color: #fff;
}
</style>
@endPushOnce
