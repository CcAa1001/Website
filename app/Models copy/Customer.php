<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class Customer extends Model {
    use HasUuid, SoftDeletes;
    
    protected $fillable = [
        'tenant_id', 'customer_code', 'name', 'email', 'phone',
        'phone_verified_at', 'date_of_birth', 'gender',
        'address', 'city', 'postal_code',
        'loyalty_points', 'loyalty_tier', 
        'total_orders', 'total_spent',
        'notes', 'tags', 'marketing_consent'
    ];
    
    protected $casts = [
        'tags' => 'array',
        'date_of_birth' => 'date',
        'marketing_consent' => 'boolean',
        'total_spent' => 'decimal:2'
    ];
}