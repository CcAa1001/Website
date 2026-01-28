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

    protected $table = 'products';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'sku',
        'name',
        'slug',
        'description',
        'image_url', // DEPRECATED - kept for backward compatibility
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = \Str::slug($product->name);
            }
        });

        // Delete all images when product is deleted
        static::deleting(function ($product) {
            $product->images()->each(function ($image) {
                $image->deleteFiles();
                $image->delete();
            });
        });
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function outletProducts(): HasMany
    {
        return $this->hasMany(OutletProduct::class);
    }

    public function modifierGroups()
    {
        return $this->belongsToMany(ModifierGroup::class, 'product_modifier_groups')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /**
     * NEW: Get all product images
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->ordered();
    }

    /**
     * NEW: Get primary image
     */
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->base_price, 0, ',', '.');
    }

    /**
     * UPDATED: Get primary image or fallback
     */
    public function getImageAttribute(): string
    {
        // Try to get primary image
        $primaryImage = $this->primaryImage;
        if ($primaryImage) {
            return $primaryImage->medium_url;
        }

        // Try to get first image
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->medium_url;
        }

        // DEPRECATED: Fallback to old image_url field
        if ($this->image_url) {
            if (filter_var($this->image_url, FILTER_VALIDATE_URL)) {
                return $this->image_url;
            }
            return asset('storage/' . $this->image_url);
        }
        
        // Use backend-controlled placeholder
        return ImageSetting::placeholderUrl($this->tenant_id);
    }

    /**
     * NEW: Get image by size
     */
    public function getImageBySize(string $size = 'medium'): string
    {
        $primaryImage = $this->primaryImage;
        
        if ($primaryImage) {
            return $primaryImage->getUrl($size);
        }

        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->getUrl($size);
        }

        return ImageSetting::placeholderUrl($this->tenant_id);
    }

    /**
     * NEW: Get thumbnail
     */
    public function getThumbnailAttribute(): string
    {
        return $this->getImageBySize('thumbnail');
    }

    /**
     * NEW: Get medium image
     */
    public function getMediumImageAttribute(): string
    {
        return $this->getImageBySize('medium');
    }

    /**
     * NEW: Get large image
     */
    public function getLargeImageAttribute(): string
    {
        return $this->getImageBySize('large');
    }

    /**
     * NEW: Check if product has images
     */
    public function getHasImagesAttribute(): bool
    {
        return $this->images()->exists();
    }

    /**
     * NEW: Get image count
     */
    public function getImageCountAttribute(): int
    {
        return $this->images()->count();
    }

    public function getHasDiscountAttribute(): bool
    {
        return false; // Extend with sale_price if needed
    }

    public function getDiscountPercentageAttribute(): int
    {
        return 0; // Extend with sale_price if needed
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeInCategorySlug($query, $slug)
    {
        return $query->whereHas('category', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }

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

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'ILIKE', "%{$term}%")
              ->orWhere('description', 'ILIKE', "%{$term}%")
              ->orWhere('sku', 'ILIKE', "%{$term}%");
        });
    }

    public function scopeWithTags($query, array $tags)
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

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

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * NEW: Eager load images
     */
    public function scopeWithImages($query)
    {
        return $query->with(['images' => function ($q) {
            $q->ordered();
        }]);
    }

    /**
     * NEW: Eager load primary image only
     */
    public function scopeWithPrimaryImage($query)
    {
        return $query->with('primaryImage');
    }
}