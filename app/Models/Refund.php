<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'outlet_id',
        'order_id',
        'payment_id',
        'refund_number',
        'refund_type',
        'subtotal_refund',
        'tax_refund',
        'service_charge_refund',
        'total_refund_amount',
        'reason',
        'notes',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'status',
        'refund_method',
        'processed_at',
    ];

    protected $casts = [
        'subtotal_refund' => 'decimal:2',
        'tax_refund' => 'decimal:2',
        'service_charge_refund' => 'decimal:2',
        'total_refund_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($refund) {
            if (empty($refund->refund_number)) {
                $refund->refund_number = self::generateRefundNumber($refund->outlet_id);
            }
            if (empty($refund->requested_at)) {
                $refund->requested_at = now();
            }
        });
    }

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RefundItem::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('requested_at', today());
    }

    /**
     * Generate refund number
     */
    public static function generateRefundNumber(string $outletId): string
    {
        $date = now()->format('Ymd');
        $prefix = 'REF';
        
        $count = self::where('outlet_id', $outletId)
                     ->whereDate('created_at', today())
                     ->count() + 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }

    /**
     * Approve refund
     */
    public function approve(string $approvedBy, ?string $refundMethod = null): bool
    {
        $this->status = 'approved';
        $this->approved_by = $approvedBy;
        $this->approved_at = now();
        $this->refund_method = $refundMethod ?? 'cash';
        
        return $this->save();
    }

    /**
     * Reject refund
     */
    public function reject(string $rejectedBy, string $reason): bool
    {
        $this->status = 'rejected';
        $this->rejected_by = $rejectedBy;
        $this->rejected_at = now();
        $this->rejection_reason = $reason;
        
        return $this->save();
    }

    /**
     * Complete refund (process payment)
     */
    public function complete(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $this->status = 'completed';
        $this->processed_at = now();
        
        return $this->save();
    }

    /**
     * Get reason label
     */
    public function getReasonLabelAttribute(): string
    {
        return match($this->reason) {
            'wrong_order' => 'Wrong Order',
            'quality_issue' => 'Food Quality Issue',
            'customer_complaint' => 'Customer Complaint',
            'staff_error' => 'Staff Error',
            'other' => 'Other',
            default => ucfirst($this->reason),
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'completed' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Check if refund can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if refund can be rejected
     */
    public function canBeRejected(): bool
    {
        return $this->status === 'pending';
    }
}