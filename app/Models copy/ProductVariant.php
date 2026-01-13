<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class ProductVariant extends Model
{
    use HasUuid;

    protected $fillable = [
        'product_id', 'sku', 'name', 'price_adjustment', 
        'cost_adjustment', 'is_default', 'is_available', 'sort_order'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_available' => 'boolean',
        'price_adjustment' => 'decimal:2',
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }
}