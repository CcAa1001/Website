<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use App\Traits\HasUuid;
class OutletInventory extends Model { use HasUuid;
    protected $table = 'outlet_inventory';
    public $timestamps = false; // Custom timestamp handling
    protected $fillable = ['outlet_id', 'inventory_item_id', 'quantity', 'last_restock_at', 'last_count_at'];
    protected $casts = ['quantity' => 'decimal:4', 'last_restock_at' => 'datetime'];
}