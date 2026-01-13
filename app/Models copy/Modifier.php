<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use App\Traits\HasUuid;
class Modifier extends Model { use HasUuid;
    protected $fillable = ['modifier_group_id', 'name', 'price', 'cost_price', 'is_available', 'sort_order'];
    protected $casts = ['price' => 'decimal:2', 'is_available' => 'boolean'];
}