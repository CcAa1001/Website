<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use App\Traits\HasUuid;
class ModifierGroup extends Model { use HasUuid;
    protected $fillable = ['tenant_id', 'name', 'selection_type', 'min_selections', 'max_selections', 'is_required', 'is_active'];
    protected $casts = ['is_required' => 'boolean', 'is_active' => 'boolean'];
    public function modifiers() { return $this->hasMany(Modifier::class); }
}