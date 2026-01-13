<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class Outlet extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'code', 'name', 'outlet_type',
        'address', 'city', 'phone', 'email',
        'latitude', 'longitude', 'operating_hours',
        'tax_rate', 'service_charge_rate', 'is_active'
    ];

    protected $casts = [
        'operating_hours' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}