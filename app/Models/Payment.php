<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    public const DIRECTION_RECEIVED = 'received';
    public const DIRECTION_PAID = 'paid';

    protected $fillable = [
        'payment_number',
        'direction',
        'payment_date',
        'customer_id',
        'vendor_id',
        'purchase_order_id',
        'invoice_id',
        'amount',
        'payment_mode',
        'reference_number',
        'remarks',
        'status',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(Procurement::class, 'purchase_order_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getModeLabelAttribute(): string
    {
        return match ($this->payment_mode) {
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'neft' => 'NEFT',
            'rtgs' => 'RTGS',
            'upi' => 'UPI',
            default => ucfirst(str_replace('_', ' ', $this->payment_mode)),
        };
    }

    public function getPartyNameAttribute(): string
    {
        return $this->direction === self::DIRECTION_RECEIVED
            ? ($this->customer?->name ?? '-')
            : ($this->vendor?->name ?? '-');
    }
}
