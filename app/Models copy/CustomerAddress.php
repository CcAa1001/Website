<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class CustomerAddress extends Model
{
    use HasUuid;

    protected $fillable = [
        'customer_id', 'label', 'recipient_name', 'phone',
        'address_line_1', 'address_line_2', 'city', 'province',
        'postal_code', 'latitude', 'longitude', 'notes', 'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
}