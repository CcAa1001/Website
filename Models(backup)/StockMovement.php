<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use App\Traits\HasUuid;
class StockMovement extends Model { use HasUuid;
    public $timestamps = false; // Only created_at
    protected $fillable = ['outlet_id', 'inventory_item_id', 'user_id', 'movement_type', 'reference_type', 'reference_id', 'quantity', 'unit_cost', 'total_cost', 'quantity_before', 'quantity_after', 'notes', 'created_at'];
    protected $casts = ['quantity' => 'decimal:4', 'created_at' => 'datetime'];
}