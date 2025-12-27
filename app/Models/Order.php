<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    // Added table_number and status to allow database saving
    protected $fillable = [
        'user_id', 
        'customer_id', 
        'table_number', 
        'total_amount', 
        'status'
    ];

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    public function customer() {
        return $this->belongsTo(Customer::class);
    }
}