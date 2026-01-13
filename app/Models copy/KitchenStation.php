<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use App\Traits\HasUuid;
class KitchenStation extends Model { use HasUuid;
    protected $fillable = ['outlet_id', 'name', 'printer_config', 'is_active'];
    protected $casts = ['printer_config' => 'array', 'is_active' => 'boolean'];
}