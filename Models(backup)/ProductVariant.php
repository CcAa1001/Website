<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price_adjustment',
        'cost_adjustment',
        'is_default',
        'is_available',
        'sort_order',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'cost_adjustment' => 'decimal:2',
        'is_default' => 'boolean',
        'is_available' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relationships
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get final price (base + adjustment)
     */
    public function getFinalPriceAttribute(): float
    {
        return $this->product->base_price + $this->price_adjustment;
    }

    /**
     * Get formatted final price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->final_price, 0, ',', '.');
    }
}
