<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes; use App\Traits\HasUuid;
class InventoryItem extends Model { use HasUuid, SoftDeletes;
    protected $fillable = ['tenant_id', 'supplier_id', 'sku', 'name', 'description', 'unit', 'cost_per_unit', 'reorder_level', 'reorder_quantity', 'is_active'];
    protected $casts = ['cost_per_unit' => 'decimal:4', 'is_active' => 'boolean'];
}