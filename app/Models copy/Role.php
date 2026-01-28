<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Role extends Model
{
    use HasUuid;

    protected $fillable = [
        'tenant_id', 'name', 'slug', 'permissions', 'is_system'
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
    ];

    public function users() {
        return $this->hasMany(User::class);
    }
}