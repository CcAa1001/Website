<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'outlet_type',
        'address',
        'city',
        'phone',
        'email',
        'latitude',
        'longitude',
        'operating_hours',
        'tax_rate',
        'service_charge_rate',
        'is_active',
    ];

    protected $casts = [
        'operating_hours' => 'array',
        'tax_rate' => 'decimal:2',
        'service_charge_rate' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function tableAreas(): HasMany
    {
        return $this->hasMany(TableArea::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tableSessions(): HasMany
    {
        return $this->hasMany(TableSession::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if outlet is currently open
     */
    public function isOpen(): bool
    {
        if (!$this->operating_hours) {
            return true; // If no hours set, assume always open
        }

        $now = now();
        $dayName = strtolower($now->format('l')); // monday, tuesday, etc.
        
        if (!isset($this->operating_hours[$dayName])) {
            return false;
        }

        $hours = $this->operating_hours[$dayName];
        $openTime = $hours['open'] ?? null;
        $closeTime = $hours['close'] ?? null;

        if (!$openTime || !$closeTime) {
            return false;
        }

        $currentTime = $now->format('H:i');
        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }
}