<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'image_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParentOnly($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get image with fallback
     */
    public function getImageAttribute(): ?string
    {
        return $this->image_url ? asset('storage/' . $this->image_url) : null;
    }

    /**
     * Get all descendant category IDs (for nested filtering)
     */
    public function getAllChildrenIds(): array
    {
        $ids = [$this->id];
        
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }
        
        return $ids;
    }
}
