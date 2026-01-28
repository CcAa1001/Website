<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUuids, SoftDeletes;

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

    protected $casts = [
        'base_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'tax_inclusive' => 'boolean',
        'is_taxable' => 'boolean',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'preparation_time' => 'integer',
        'calories' => 'integer',
        'sort_order' => 'integer',
        'tags' => 'array',
        'allergens' => 'array',
    ];

    /**
     * Relationships
     */
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

    public function modifierGroups(): BelongsToMany
    {
        return $this->belongsToMany(ModifierGroup::class, 'product_modifier_groups')
                    ->withPivot('sort_order')
                    ->orderByPivot('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scopes
     */
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

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the default variant
     */
    public function getDefaultVariantAttribute()
    {
        return $this->variants->where('is_default', true)->first() 
            ?? $this->variants->first();
    }

    /**
     * Get price range for variant products
     */
    public function getPriceRangeAttribute(): array
    {
        if ($this->product_type !== 'variant' || $this->variants->isEmpty()) {
            return ['min' => $this->base_price, 'max' => $this->base_price];
        }

        $prices = $this->variants->map(function ($v) {
            return $this->base_price + $v->price_adjustment;
        });

        return [
            'min' => $prices->min(),
            'max' => $prices->max(),
        ];
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->base_price, 0, ',', '.');
    }

    /**
     * Get image URL with fallback
     */
    public function getImageAttribute(): string
    {
        if ($this->image_url) {
            return asset('storage/' . $this->image_url);
        }
        return asset('assets/img/products/default.jpg');
    }
}
