<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'products';

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
        'category_id',
        'sku',
        'name',
        'slug',
        'description',
        'image_url',
        'base_price',
        'cost_price',
        'tax_inclusive',
        'is_taxable',
        'product_type',
        'preparation_time',
        'calories',
        'is_available',
        'is_featured',
        'sort_order',
        'tags',
        'allergens',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'base_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'tax_inclusive' => 'boolean',
        'is_taxable' => 'boolean',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'tags' => 'array',
        'allergens' => 'array',
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
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = \Str::slug($product->name);
            }
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the tenant that owns the product.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the category that the product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /**
     * Get the outlet-specific product settings.
     */
    public function outletProducts(): HasMany
    {
        return $this->hasMany(OutletProduct::class);
    }

    /**
     * Get the modifier groups for the product.
     */
    public function modifierGroups()
    {
        return $this->belongsToMany(ModifierGroup::class, 'product_modifier_groups')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->base_price, 0, ',', '.');
    }

    /**
     * Get the product image URL or default placeholder.
     */
    public function getImageAttribute(): string
    {
        if ($this->image_url) {
            // Check if it's a full URL or relative path
            if (filter_var($this->image_url, FILTER_VALIDATE_URL)) {
                return $this->image_url;
            }
            return asset('storage/' . $this->image_url);
        }
        
        return asset('assets/images/product-placeholder.png');
    }

    /**
     * Check if product has a discount/sale price.
     */
    public function getHasDiscountAttribute(): bool
    {
        // You can extend this with sale_price field if needed
        return false;
    }

    /**
     * Get discount percentage.
     */
    public function getDiscountPercentageAttribute(): int
    {
        // Extend this when you add sale_price
        return 0;
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to filter only available products.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope to filter featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to filter by category slug.
     */
    public function scopeInCategorySlug($query, $slug)
    {
        return $query->whereHas('category', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }

    /**
     * Scope to filter by price range.
     */
    public function scopePriceBetween($query, $min, $max)
    {
        if ($min !== null) {
            $query->where('base_price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('base_price', '<=', $max);
        }
        return $query;
    }

    /**
     * Scope to search products by name or description.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'ILIKE', "%{$term}%")
              ->orWhere('description', 'ILIKE', "%{$term}%")
              ->orWhere('sku', 'ILIKE', "%{$term}%");
        });
    }

    /**
     * Scope to filter by tags.
     */
    public function scopeWithTags($query, array $tags)
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    /**
     * Scope to sort products.
     */
    public function scopeSortBy($query, $sort)
    {
        return match ($sort) {
            'price_low' => $query->orderBy('base_price', 'asc'),
            'price_high' => $query->orderBy('base_price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            'featured' => $query->orderBy('is_featured', 'desc')->orderBy('sort_order', 'asc'),
            default => $query->orderBy('sort_order', 'asc'),
        };
    }

    /**
     * Scope for a specific tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
