<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use App\Traits\HasUuid;
class Payment extends Model { use HasUuid;
    protected $fillable = ['tenant_id', 'outlet_id', 'order_id', 'payment_method_id', 'user_id', 'payment_number', 'transaction_type', 'amount', 'net_amount', 'gateway_transaction_id', 'gateway_response', 'status', 'cash_received', 'cash_change', 'paid_at'];
    protected $casts = ['gateway_response' => 'array', 'paid_at' => 'datetime', 'amount' => 'decimal:2'];
    public function order() { return $this->belongsTo(Order::class); }
}