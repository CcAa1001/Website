<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use App\Traits\HasRolePermissions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRolePermissions;

    // UUID Configuration
    protected $keyType = 'string';
    public $incrementing = false;

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

    protected $hidden = [
        'password',
        'remember_token',
        'pin_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Boot method to auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
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

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    /*
    |--------------------------------------------------------------------------
    | Role & Permission Helpers
    |--------------------------------------------------------------------------
    */

    public function hasRole(array|string $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return in_array($this->role?->slug, $roles);
    }

    public function hasPermission(string $permission): bool
    {
        // Super roles bypass everything
        if ($this->role && in_array($this->role->slug, ['super_admin', 'master_admin'])) {
            return true;
        }

        // Direct user permissions
        if ($this->permissions->contains('name', $permission)) {
            return true;
        }

        // Role-based permissions
        if ($this->role) {
            $rolePermissions = $this->role->permissions ?? [];

            if (
                is_array($rolePermissions) &&
                (in_array('*', $rolePermissions) || in_array($permission, $rolePermissions))
            ) {
                return true;
            }
        }

        return false;
    }
}
