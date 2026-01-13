<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use App\Traits\HasUuid;
class OrderItemModifier extends Model { use HasUuid;
    public $timestamps = false;
    protected $fillable = ['order_item_id', 'modifier_id', 'modifier_name', 'quantity', 'unit_price', 'subtotal', 'created_at'];
}