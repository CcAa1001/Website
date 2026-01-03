<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class Product extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'category_id', 'sku', 'name', 'slug',
        'description', 'image_url', 'base_price', 'cost_price',
        'tax_inclusive', 'is_taxable', 'product_type',
        'preparation_time', 'calories', 'is_available',
        'is_featured', 'sort_order', 'tags', 'allergens'
    ];

    protected $casts = [
        'tags' => 'array',
        'allergens' => 'array',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'tax_inclusive' => 'boolean',
        'is_taxable' => 'boolean',
        'base_price' => 'decimal:2',
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }
}