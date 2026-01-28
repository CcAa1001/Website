<?php

namespace App\Http\Livewire\Public;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ShopProducts extends Component
{
    use WithPagination;

    // Filter states
    public ?string $selectedCategory = null;
    public string $search = '';
    public ?int $minPrice = null;
    public ?int $maxPrice = null;
    public string $sortBy = 'default';
    public array $selectedTags = [];
    public bool $featuredOnly = false;

    // UI states
    public int $perPage = 12;
    public string $view = 'grid';

    // Query string binding - URL updates when filters change
    protected $queryString = [
        'selectedCategory' => ['except' => '', 'as' => 'category'],
        'search' => ['except' => ''],
        'minPrice' => ['except' => null, 'as' => 'min_price'],
        'maxPrice' => ['except' => null, 'as' => 'max_price'],
        'sortBy' => ['except' => 'default', 'as' => 'sort'],
        'selectedTags' => ['except' => [], 'as' => 'tags'],
        'featuredOnly' => ['except' => false, 'as' => 'featured'],
    ];

    // Reset pagination when filters change
    public function updatingSelectedCategory() { $this->resetPage(); }
    public function updatingSearch() { $this->resetPage(); }
    public function updatingMinPrice() { $this->resetPage(); }
    public function updatingMaxPrice() { $this->resetPage(); }
    public function updatingSortBy() { $this->resetPage(); }
    public function updatingSelectedTags() { $this->resetPage(); }
    public function updatingFeaturedOnly() { $this->resetPage(); }

    // Actions
    public function selectCategory(?string $slug)
    {
        $this->selectedCategory = $slug;
        $this->resetPage();
        $this->dispatch('category-changed', category: $slug);
    }

    public function clearCategory()
    {
        $this->selectedCategory = null;
        $this->resetPage();
    }

    public function toggleTag(string $tag)
    {
        if (in_array($tag, $this->selectedTags)) {
            $this->selectedTags = array_values(array_diff($this->selectedTags, [$tag]));
        } else {
            $this->selectedTags[] = $tag;
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['selectedCategory', 'search', 'minPrice', 'maxPrice', 'sortBy', 'selectedTags', 'featuredOnly']);
        $this->resetPage();
    }

    public function setView(string $view)
    {
        $this->view = $view;
    }

    public function loadMore()
    {
        $this->perPage += 12;
    }

    public function render()
    {
        // Build product query
        $query = Product::query()
            ->available()
            ->with(['category', 'variants']);

        // Category filter
        if ($this->selectedCategory) {
            $category = Category::where('slug', $this->selectedCategory)->first();
            if ($category) {
                $categoryIds = $category->getAllDescendantIds();
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Search filter
        if (!empty($this->search)) {
            $query->search($this->search);
        }

        // Price range filter
        if ($this->minPrice !== null || $this->maxPrice !== null) {
            $query->priceBetween($this->minPrice, $this->maxPrice);
        }

        // Tags filter
        if (!empty($this->selectedTags)) {
            $query->withTags($this->selectedTags);
        }

        // Featured filter
        if ($this->featuredOnly) {
            $query->featured();
        }

        // Sorting
        $query->sortBy($this->sortBy);

        // Get paginated products
        $products = $query->paginate($this->perPage);

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

        // Get current category
        $currentCategory = $this->selectedCategory 
            ? Category::where('slug', $this->selectedCategory)->first() 
            : null;

        // Get subcategories
        $subcategories = $currentCategory 
            ? $currentCategory->children()->active()->ordered()->withCount(['products' => fn($q) => $q->available()])->get()
            : collect();

        // Get price range
        $priceRangeQuery = Product::query()->available();
        if ($currentCategory) {
            $priceRangeQuery->whereIn('category_id', $currentCategory->getAllDescendantIds());
        }
        $priceRange = [
            'min' => (int) ($priceRangeQuery->min('base_price') ?? 0),
            'max' => (int) ($priceRangeQuery->max('base_price') ?? 1000000),
        ];

        // Get available tags
        $tagsQuery = Product::query()->available()->whereNotNull('tags');
        if ($currentCategory) {
            $tagsQuery->whereIn('category_id', $currentCategory->getAllDescendantIds());
        }
        $allTags = [];
        foreach ($tagsQuery->pluck('tags') as $productTags) {
            if (is_array($productTags)) {
                $allTags = array_merge($allTags, $productTags);
            }
        }
        $availableTags = array_unique($allTags);

        // Count active filters
        $activeFiltersCount = 0;
        if ($this->selectedCategory) $activeFiltersCount++;
        if (!empty($this->search)) $activeFiltersCount++;
        if ($this->minPrice !== null) $activeFiltersCount++;
        if ($this->maxPrice !== null) $activeFiltersCount++;
        if (!empty($this->selectedTags)) $activeFiltersCount += count($this->selectedTags);
        if ($this->featuredOnly) $activeFiltersCount++;

        return view('livewire.public.shop-products', [
            'products' => $products,
            'categories' => $categories,
            'sliderCategories' => $sliderCategories,
            'currentCategory' => $currentCategory,
            'subcategories' => $subcategories,
            'priceRange' => $priceRange,
            'availableTags' => $availableTags,
            'activeFiltersCount' => $activeFiltersCount,
        ]);
    }
}
