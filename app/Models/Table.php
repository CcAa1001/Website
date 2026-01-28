<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'table_area_id',
        'table_number',
        'capacity',
        'status',
        'current_order_id',
        'qr_code', // Wajib ada untuk menyimpan kode
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // --- RELATIONSHIPS ---

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function tableArea(): BelongsTo
    {
        return $this->belongsTo(TableArea::class, 'table_area_id');
    }

    // Alias 'area' agar kompatibel dengan kode yang memanggil $table->area
    public function area()
    {
        return $this->tableArea();
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

    // --- SCOPES ---

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

    // --- HELPER METHODS ---

    public function hasActiveSession(): bool
    {
        return $this->activeSession()->exists();
    }

    public function getOrCreateSession(?string $deviceFingerprint = null): TableSession
    {
        $session = $this->activeSession;
        
        if ($session) {
            $session->touch();
            return $session;
        }

        $session = $this->sessions()->create([
            'outlet_id' => $this->outlet_id,
            'tenant_id' => $this->outlet->tenant_id,
            'device_fingerprint' => $deviceFingerprint,
            'status' => 'active',
        ]);

        $this->update(['status' => 'occupied']);

        return $session;
    }

    /**
     * [IMPROVED] Generate Smart QR Code URL
     * Membedakan antara Link Eksternal (Google) dan Internal (Menu).
     */
    public function getQrUrlAttribute(): string
    {
        // 1. Jika kode adalah URL valid (https://...), return langsung
        if (filter_var($this->qr_code, FILTER_VALIDATE_URL)) {
            return $this->qr_code;
        }

        // 2. Jika kode biasa (MEJA-01), return route scan internal
        return route('table.scan', ['qr_code' => $this->qr_code]);
    }

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