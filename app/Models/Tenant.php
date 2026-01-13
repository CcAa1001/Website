<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'tenants';

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'legal_name',
        'tax_id',
        'business_type',
        'logo_url',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
        'country',
        'currency',
        'timezone',
        'subscription_plan',
        'subscription_expires_at',
        'settings',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'subscription_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the outlets for the tenant.
     */
    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    /**
     * Get the categories for the tenant.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the products for the tenant.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the users for the tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the customers for the tenant.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get the logo URL or default.
     */
    public function getLogoAttribute(): string
    {
        if ($this->logo_url) {
            if (filter_var($this->logo_url, FILTER_VALIDATE_URL)) {
                return $this->logo_url;
            }
            return asset('storage/' . $this->logo_url);
        }
        
        return asset('assets/images/logo-placeholder.png');
    }

    /**
     * Check if subscription is active.
     */
    public function getIsSubscriptionActiveAttribute(): bool
    {
        if (!$this->subscription_expires_at) {
            return true; // No expiry set means active
        }
        
        return $this->subscription_expires_at->isFuture();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to filter only active tenants.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
