<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Table extends Model
{
    use HasUuid;

    protected $fillable = [
        'outlet_id', 'table_area_id', 'table_number', 'capacity',
        'status', 'current_order_id', 'qr_code', 'sort_order', 'is_active'
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function area() {
        return $this->belongsTo(TableArea::class, 'table_area_id');
    }
}