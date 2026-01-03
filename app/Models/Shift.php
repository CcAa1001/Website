<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use App\Traits\HasUuid;
class Shift extends Model { use HasUuid;
    protected $fillable = ['outlet_id', 'user_id', 'shift_number', 'opening_cash', 'expected_cash', 'actual_cash', 'cash_difference', 'total_sales', 'payment_breakdown', 'status', 'notes', 'opened_at', 'closed_at'];
    protected $casts = ['payment_breakdown' => 'array', 'opened_at' => 'datetime', 'closed_at' => 'datetime', 'opening_cash' => 'decimal:2'];
}