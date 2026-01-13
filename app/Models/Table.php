<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Table extends Model
{
    use HasUuids;

    protected $fillable = [
        'outlet_id',
        'table_area_id',
        'table_number',
        'capacity',
        'status',
        'current_order_id',
        'qr_code',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function tableArea(): BelongsTo
    {
        return $this->belongsTo(TableArea::class);
    }

    public function currentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'current_order_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TableSession::class);
    }

    public function activeSession(): HasOne
    {
        return $this->hasOne(TableSession::class)
                    ->whereIn('status', ['active', 'ordering', 'billing'])
                    ->where('expires_at', '>', now())
                    ->latest();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    public function scopeForOutlet($query, $outletId)
    {
        return $query->where('outlet_id', $outletId);
    }

    public function scopeByQrCode($query, $qrCode)
    {
        return $query->where('qr_code', $qrCode);
    }

    /**
     * Check if table has active session
     */
    public function hasActiveSession(): bool
    {
        return $this->activeSession()->exists();
    }

    /**
     * Get or create active session
     */
    public function getOrCreateSession(?string $deviceFingerprint = null): TableSession
    {
        // Check for existing active session
        $session = $this->activeSession;
        
        if ($session) {
            // Update activity
            $session->touch();
            return $session;
        }

        // Create new session
        $session = $this->sessions()->create([
            'outlet_id' => $this->outlet_id,
            'tenant_id' => $this->outlet->tenant_id,
            'device_fingerprint' => $deviceFingerprint,
            'status' => 'active',
        ]);

        // Update table status
        $this->update(['status' => 'occupied']);

        return $session;
    }

    /**
     * Generate QR code URL
     */
    public function getQrUrlAttribute(): string
    {
        return route('table.scan', ['qr_code' => $this->qr_code]);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'available' => 'success',
            'occupied' => 'warning',
            'reserved' => 'info',
            'cleaning' => 'secondary',
            default => 'dark',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'available' => 'Tersedia',
            'occupied' => 'Terisi',
            'reserved' => 'Dipesan',
            'cleaning' => 'Dibersihkan',
            default => $this->status,
        };
    }
}
