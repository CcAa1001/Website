<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    /**
     * Number of products per page.
     */
    protected int $perPage = 12;

    /**
     * Display the shop page with all products.
     */
    public function index(Request $request): View
    {
        // Get filter parameters
        $filters = $this->getFilters($request);

        // Build product query
        $productsQuery = Product::query()
            ->available()
            ->with(['category', 'variants']);

        // Apply filters
        $productsQuery = $this->applyFilters($productsQuery, $filters);

        // Get paginated products
        $products = $productsQuery->paginate($this->perPage)->withQueryString();

        // Get categories for sidebar filter
        $categories = Category::query()
            ->active()
            ->parents()
            ->withCount(['products' => fn($q) => $q->available()])
            ->with(['children' => fn($q) => $q->active()->withCount(['products' => fn($q) => $q->available()])])
            ->ordered()
            ->get();

        // Get all categories for the slider
        $sliderCategories = Category::query()
            ->active()
            ->parents()
            ->ordered()
            ->take(10)
            ->get();

        // Get price range for filter
        $priceRange = $this->getPriceRange();

        // Get available tags for filter
        $availableTags = $this->getAvailableTags();

        return view('public.shop.index', compact(
            'products',
            'categories',
            'sliderCategories',
            'filters',
            'priceRange',
            'availableTags'
        ));
    }

    /**
     * Display products by category.
     */
    public function category(Request $request, string $slug): View
    {
        // Find category by slug
        $category = Category::where('slug', $slug)
            ->active()
            ->firstOrFail();

        // Get all category IDs (including children) for filtering
        $categoryIds = $category->getAllDescendantIds();

        // Get filter parameters
        $filters = $this->getFilters($request);
        $filters['category'] = $slug;

        // Build product query
        $productsQuery = Product::query()
            ->available()
            ->whereIn('category_id', $categoryIds)
            ->with(['category', 'variants']);

        // Apply filters (except category since we already filtered)
        $filtersWithoutCategory = $filters;
        unset($filtersWithoutCategory['category']);
        $productsQuery = $this->applyFilters($productsQuery, $filtersWithoutCategory);

        // Get paginated products
        $products = $productsQuery->paginate($this->perPage)->withQueryString();

        // Get categories for sidebar
        $categories = Category::query()
            ->active()
            ->parents()
            ->withCount(['products' => fn($q) => $q->available()])
            ->with(['children' => fn($q) => $q->active()->withCount(['products' => fn($q) => $q->available()])])
            ->ordered()
            ->get();

        // Get subcategories of current category
        $subcategories = $category->children()->active()->ordered()->get();

        // Get slider categories
        $sliderCategories = Category::query()
            ->active()
            ->parents()
            ->ordered()
            ->take(10)
            ->get();

        // Get price range
        $priceRange = $this->getPriceRange($categoryIds);

        // Get available tags
        $availableTags = $this->getAvailableTags($categoryIds);

        return view('public.shop.index', compact(
            'products',
            'categories',
            'category',
            'subcategories',
            'sliderCategories',
            'filters',
            'priceRange',
            'availableTags'
        ));
    }

    /**
     * Search products.
     */
    public function search(Request $request): View
    {
        $query = $request->input('q', '');

        // Get filter parameters
        $filters = $this->getFilters($request);
        $filters['search'] = $query;

        // Build product query
        $productsQuery = Product::query()
            ->available()
            ->with(['category', 'variants']);

        // Apply search
        if (!empty($query)) {
            $productsQuery->search($query);
        }

        // Apply other filters
        $filtersWithoutSearch = $filters;
        unset($filtersWithoutSearch['search']);
        $productsQuery = $this->applyFilters($productsQuery, $filtersWithoutSearch);

        // Get paginated products
        $products = $productsQuery->paginate($this->perPage)->withQueryString();

        // Get categories for sidebar
        $categories = Category::query()
            ->active()
            ->parents()
            ->withCount(['products' => fn($q) => $q->available()])
            ->with(['children' => fn($q) => $q->active()->withCount(['products' => fn($q) => $q->available()])])
            ->ordered()
            ->get();

        // Get slider categories
        $sliderCategories = Category::query()
            ->active()
            ->parents()
            ->ordered()
            ->take(10)
            ->get();

        // Get price range
        $priceRange = $this->getPriceRange();

        // Get available tags
        $availableTags = $this->getAvailableTags();

        return view('public.shop.index', compact(
            'products',
            'categories',
            'sliderCategories',
            'filters',
            'priceRange',
            'availableTags',
            'query'
        ));
    }

    /**
     * Display single product details.
     */
    public function show(string $slug): View
    {
        $product = Product::where('slug', $slug)
            ->available()
            ->with(['category', 'variants', 'modifierGroups.modifiers'])
            ->firstOrFail();

        // Get related products from same category
        $relatedProducts = Product::query()
            ->available()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->with(['category'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('public.shop.show', compact('product', 'relatedProducts'));
    }

    /**
     * Get filter parameters from request.
     */
    protected function getFilters(Request $request): array
    {
        return [
            'category' => $request->input('category'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'sort' => $request->input('sort', 'default'),
            'search' => $request->input('q'),
            'tags' => $request->input('tags', []),
            'featured' => $request->boolean('featured'),
        ];
    }

    /**
     * Apply filters to product query.
     */
    protected function applyFilters($query, array $filters)
    {
        // Category filter
        if (!empty($filters['category'])) {
            $query->inCategorySlug($filters['category']);
        }

        // Price range filter
        if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
            $query->priceBetween(
                $filters['min_price'] ? (float) $filters['min_price'] : null,
                $filters['max_price'] ? (float) $filters['max_price'] : null
            );
        }

        // Search filter
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Tags filter
        if (!empty($filters['tags']) && is_array($filters['tags'])) {
            $query->withTags($filters['tags']);
        }

        // Featured filter
        if (!empty($filters['featured'])) {
            $query->featured();
        }

        // Sorting
        $query->sortBy($filters['sort'] ?? 'default');

        return $query;
    }

    /**
     * Get price range for filter.
     */
    protected function getPriceRange(?array $categoryIds = null): array
    {
        $query = Product::query()->available();

        if ($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        }

        return [
            'min' => (int) $query->min('base_price') ?? 0,
            'max' => (int) $query->max('base_price') ?? 1000000,
        ];
    }

    /**
     * Get available tags for filter.
     */
    protected function getAvailableTags(?array $categoryIds = null): array
    {
        $query = Product::query()->available()->whereNotNull('tags');

        if ($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        }

        $products = $query->pluck('tags');

        $tags = [];
        foreach ($products as $productTags) {
            if (is_array($productTags)) {
                $tags = array_merge($tags, $productTags);
            }
        }

        return array_unique($tags);
    }
}
