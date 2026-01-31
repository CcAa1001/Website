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
        'tenant_id',      // [FIX] WAJIB DITAMBAHKAN AGAR BISA SAVE
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

    // --- RELATIONSHIPS ---

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function tableArea(): BelongsTo
    {
        return $this->belongsTo(TableArea::class, 'table_area_id');
    }

    // Alias 'area' 
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
}