<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'outlet_id',
        'order_id',
        'payment_method_id',
        'user_id',
        'payment_number',
        'transaction_type',
        'amount',
        'net_amount',
        'gateway_transaction_id',
        'gateway_response',
        'status',
        'cash_received',
        'cash_change',
        'paid_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'cash_change' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopePayments($query)
    {
        return $query->where('transaction_type', 'payment');
    }

    public function scopeRefunds($query)
    {
        return $query->where('transaction_type', 'refund');
    }
}