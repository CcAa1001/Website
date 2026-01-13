<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModifierGroup extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'is_required',
        'max_selections',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function modifiers()
    {
        return $this->hasMany(Modifier::class)->orderBy('sort_order');
    }
}