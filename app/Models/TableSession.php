<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TableSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'table_id',
        'outlet_id',
        'tenant_id',
        'customer_id',
        'session_token',
        'device_fingerprint',
        'guest_count',
        'started_at',
        'last_activity_at',
        'expires_at',
        'closed_at',
        'closed_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
        'closed_at' => 'datetime',
        'guest_count' => 'integer',
    ];

    // Session duration in hours
    const SESSION_DURATION_HOURS = 4;
    const INACTIVITY_TIMEOUT_MINUTES = 60;

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if (empty($session->session_token)) {
                $session->session_token = self::generateToken();
            }
            if (empty($session->started_at)) {
                $session->started_at = now();
            }
            if (empty($session->last_activity_at)) {
                $session->last_activity_at = now();
            }
            if (empty($session->expires_at)) {
                $session->expires_at = now()->addHours(self::SESSION_DURATION_HOURS);
            }
        });
    }

    /**
     * Generate unique session token
     */
    public static function generateToken(): string
    {
        do {
            $token = Str::random(64);
        } while (self::where('session_token', $token)->exists());

        return $token;
    }

    /**
     * Relationships
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_session_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'ordering', 'billing'])
                     ->where('expires_at', '>', now());
    }

    public function scopeForTable($query, $tableId)
    {
        return $query->where('table_id', $tableId);
    }

    public function scopeByToken($query, $token)
    {
        return $query->where('session_token', $token);
    }

    /**
     * Check if session is active
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'ordering', 'billing']) 
               && $this->expires_at > now();
    }

    /**
     * Check if session is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at <= now() || $this->status === 'expired';
    }

    /**
     * Update last activity timestamp (renamed from touch)
     */
    public function updateActivity(): bool
    {
        $this->last_activity_at = now();
        return $this->save();
    }

    /**
     * Extend session
     */
    public function extend(int $hours = null): bool
    {
        $hours = $hours ?? self::SESSION_DURATION_HOURS;
        $this->expires_at = now()->addHours($hours);
        $this->last_activity_at = now();
        return $this->save();
    }

    /**
     * Close the session
     */
    public function close(?string $closedBy = null): bool
    {
        $this->status = 'completed';
        $this->closed_at = now();
        $this->closed_by = $closedBy;
        
        // Update table status
        if ($this->table) {
            $this->table->update(['status' => 'available', 'current_order_id' => null]);
        }
        
        return $this->save();
    }

    /**
     * Get total amount from all orders in this session
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->orders()->sum('grand_total');
    }

    /**
     * Get order count
     */
    public function getOrderCountAttribute(): int
    {
        return $this->orders()->count();
    }

    /**
     * Get duration in minutes
     */
    public function getDurationMinutesAttribute(): int
    {
        $end = $this->closed_at ?? now();
        return $this->started_at->diffInMinutes($end);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->duration_minutes;
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}j {$mins}m";
        }
        return "{$mins} menit";
    }
}