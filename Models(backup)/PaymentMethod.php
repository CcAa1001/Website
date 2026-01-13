<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use App\Traits\HasUuid;
class PaymentMethod extends Model { use HasUuid;
    protected $fillable = ['tenant_id', 'code', 'name', 'payment_type', 'gateway_config', 'processing_fee_percentage', 'is_active'];
    protected $casts = ['gateway_config' => 'array', 'is_active' => 'boolean'];
}