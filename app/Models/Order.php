<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class Order extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'outlet_id', 'customer_id', 'table_id', 'user_id',
        'order_number', 'order_type', 'order_source', 'external_order_id',
        'customer_name', 'customer_phone', 'customer_email',
        'delivery_address_id', 'delivery_address', 'delivery_notes',
        'delivery_fee', 'estimated_delivery_time',
        'status', 'payment_status',
        'subtotal', 'discount_amount', 'tax_amount', 
        'service_charge', 'rounding_amount', 'grand_total',
        'notes', 'guest_count',
        'ordered_at', 'confirmed_at', 'prepared_at', 
        'completed_at', 'cancelled_at', 'cancellation_reason'
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'grand_total' => 'decimal:2',
    ];

    public function items() {
        return $this->hasMany(OrderItem::class);
    }
    
    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    // TAMBAHAN BARU: Relasi ke Meja
    public function table() {
        return $this->belongsTo(Table::class);
    }
}