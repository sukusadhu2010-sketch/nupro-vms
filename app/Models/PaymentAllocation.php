<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAllocation extends Model
{
    protected $fillable = [
        'payment_id',
        'invoice_id',
        'allocated_amount',
        'amount',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
    ];

    public function getAmountAttribute()
    {
        return $this->allocated_amount;
    }

    public function setAmountAttribute($value): void
    {
        $this->attributes['allocated_amount'] = $value;
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
