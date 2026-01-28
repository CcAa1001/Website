<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'categories';

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'image_url',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from name if not provided
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = \Str::slug($category->name);
            }
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the tenant that owns the category.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Get all descendants (recursive children).
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the products in this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get available products in this category.
     */
    public function availableProducts(): HasMany
    {
        return $this->hasMany(Product::class)
            ->where('is_available', true)
            ->orderBy('sort_order');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get the category image URL or default placeholder.
     */
    public function getImageAttribute(): string
    {
        if ($this->image_url) {
            if (filter_var($this->image_url, FILTER_VALIDATE_URL)) {
                return $this->image_url;
            }
            return asset('storage/' . $this->image_url);
        }
        
        return asset('assets/images/category-placeholder.png');
    }

    /**
     * Get the product count for this category.
     */
    public function getProductCountAttribute(): int
    {
        return $this->products()->available()->count();
    }

    /**
     * Check if category has children.
     */
    public function getHasChildrenAttribute(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get breadcrumb path to this category.
     */
    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [];
        $category = $this;
        
        while ($category) {
            array_unshift($breadcrumb, [
                'name' => $category->name,
                'slug' => $category->slug,
                'url' => route('shop.category', $category->slug),
            ]);
            $category = $category->parent;
        }
        
        return $breadcrumb;
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to filter only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only parent categories (no parent).
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get categories with products.
     */
    public function scopeWithProducts($query)
    {
        return $query->whereHas('products', function ($q) {
            $q->where('is_available', true);
        });
    }

    /**
     * Scope for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // ==========================================
    // METHODS
    // ==========================================

    /**
     * Get all category IDs including descendants.
     */
    public function getAllDescendantIds(): array
    {
        $ids = [$this->id];
        
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }
        
        return $ids;
    }
}
