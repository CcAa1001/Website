<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use App\Traits\HasUuid;
class ProductRecipe extends Model { use HasUuid;
    protected $fillable = ['product_id', 'product_variant_id', 'inventory_item_id', 'quantity'];
    protected $casts = ['quantity' => 'decimal:4'];
}