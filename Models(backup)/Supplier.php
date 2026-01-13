<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes; use App\Traits\HasUuid;
class Supplier extends Model { use HasUuid, SoftDeletes;
    protected $fillable = ['tenant_id', 'code', 'name', 'contact_person', 'email', 'phone', 'address', 'payment_terms', 'notes', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}