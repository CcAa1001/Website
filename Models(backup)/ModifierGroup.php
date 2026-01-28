<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModifierGroup extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id',
        'name',
        'selection_type',
        'min_selections',
        'max_selections',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        'min_selections' => 'integer',
        'max_selections' => 'integer',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(Modifier::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_modifier_groups')
                    ->withPivot('sort_order')
                    ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get available modifiers only
     */
    public function availableModifiers()
    {
        return $this->modifiers()->where('is_available', true)->orderBy('sort_order');
    }
}
