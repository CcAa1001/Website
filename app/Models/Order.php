<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'table_number', 'total_amount', 'status', 'user_id'
    ];

    public function items() {
        return $this->hasMany(OrderItem::class);
    }
}