<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class TableArea extends Model
{
    use HasUuid;

    protected $fillable = ['outlet_id', 'name', 'sort_order', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    
    public function tables() {
        return $this->hasMany(Table::class);
    }
}