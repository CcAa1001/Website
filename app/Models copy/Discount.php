<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes; use App\Traits\HasUuid;
class Discount extends Model { use HasUuid, SoftDeletes;
    protected $fillable = ['tenant_id', 'code', 'name', 'discount_type', 'discount_value', 'min_order_amount', 'max_discount_amount', 'valid_from', 'valid_until', 'usage_limit', 'current_usage', 'is_active'];
    protected $casts = ['valid_from' => 'datetime', 'valid_until' => 'datetime', 'is_active' => 'boolean'];
}