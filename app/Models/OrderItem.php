<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'sku',
        'quantity',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'subtotal',
        'modifiers',
        'notes',
        'kitchen_status',
        'kitchen_printed_at',
        'prepared_at',
        'served_at',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
        'modifiers' => 'array',
        'kitchen_printed_at' => 'datetime',
        'prepared_at' => 'datetime',
        'served_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('kitchen_status', 'pending');
    }

    public function scopePreparing($query)
    {
        return $query->where('kitchen_status', 'preparing');
    }

    public function scopeReady($query)
    {
        return $query->where('kitchen_status', 'ready');
    }

    public function scopeServed($query)
    {
        return $query->where('kitchen_status', 'served');
    }

    /**
     * Get kitchen status label
     */
    public function getKitchenStatusLabelAttribute(): string
    {
        return match($this->kitchen_status) {
            'pending' => 'Menunggu',
            'preparing' => 'Diproses',
            'ready' => 'Siap',
            'served' => 'Disajikan',
            'cancelled' => 'Dibatalkan',
            default => $this->kitchen_status,
        };
    }

    /**
     * Get kitchen status color
     */
    public function getKitchenStatusColorAttribute(): string
    {
        return match($this->kitchen_status) {
            'pending' => 'warning',
            'preparing' => 'info',
            'ready' => 'success',
            'served' => 'secondary',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}