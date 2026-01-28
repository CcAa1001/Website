<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class Tenant extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'legal_name', 'tax_id', 'business_type',
        'logo_url', 'email', 'phone', 'address', 'city',
        'province', 'postal_code', 'country', 'currency',
        'timezone', 'subscription_plan', 'subscription_expires_at',
        'settings', 'is_active'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'subscription_expires_at' => 'datetime',
    ];

    public function outlets()
    {
        return $this->hasMany(Outlet::class);
    }
}