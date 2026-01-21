<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    // ⭐ CRITICAL: UUID Configuration
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tenant_id',
        'outlet_id',
        'role_id',
        'employee_code',
        'name',
        'email',
        'password',
        'phone',
        'avatar_url',
        'pin_code',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin_code',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot method to auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Str::uuid();
            }
        });
    }

    /**
     * Relationships
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * CHECK PERMISSION HELPER
     * This allows you to set it freely.
     */
    public function hasPermission($permission): bool
    {
        // 1. If user has no role, deny
        if (!$this->role) {
            return false;
        }

        // 2. If user is 'super_admin' slug, ALLOW EVERYTHING
        if ($this->role->slug === 'super_admin') {
            return true;
        }

        // 3. Get permissions array from role
        $permissions = $this->role->permissions ?? [];

        // 4. If permissions contain wildcard '*', ALLOW EVERYTHING
        if (in_array('*', $permissions)) {
            return true;
        }

        // 5. Check for specific permission
        return in_array($permission, $permissions);
    }
}