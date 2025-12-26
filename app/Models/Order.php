<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    protected $fillable = ['user_id', 'customer_id', 'total_amount'];
    public function customer() { return $this->belongsTo(Customer::class); }
}