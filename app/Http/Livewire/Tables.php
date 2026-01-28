<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'outlet_id', 'table_area_id', 'table_number', 'capacity',
        'status', 'current_order_id', 'qr_code', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function outlet() { return $this->belongsTo(Outlet::class); }
    public function tableArea() { return $this->belongsTo(TableArea::class, 'table_area_id'); }
    public function area() { return $this->tableArea(); } 
    public function currentOrder() { return $this->belongsTo(Order::class, 'current_order_id'); }
    public function orders() { return $this->hasMany(Order::class); }
    public function sessions() { return $this->hasMany(TableSession::class); }

    public function activeSession() {
        return $this->hasOne(TableSession::class)
            ->whereIn('status', ['active', 'ordering', 'billing'])
            ->where('expires_at', '>', now())
            ->latest();
    }

    // [SMART LINK QR]
    public function getQrUrlAttribute(): string
    {
        // Jika URL Valid (https://google.com), return langsung
        if (filter_var($this->qr_code, FILTER_VALIDATE_URL)) {
            return $this->qr_code;
        }
        // Jika Kode Unik (MEJA-01), buat link scan internal
        return route('table.scan', ['qr_code' => $this->qr_code]);
    }
}