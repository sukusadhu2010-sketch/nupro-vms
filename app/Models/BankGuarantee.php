<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankGuarantee extends Model
{
    public const STATUSES = ['active', 'expired', 'closed', 'released', 'invoked'];

    protected $fillable = [
        'sales_order_id',
        'invoice_id',
        'bg_number',
        'bg_date',
        'expiry_date',
        'claim_date',
        'claim_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'bg_date' => 'date',
        'expiry_date' => 'date',
        'claim_date' => 'date',
        'claim_amount' => 'decimal:2',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer()
    {
        return $this->hasOneThrough(
            Customer::class,
            SalesOrder::class,
            'id',
            'sales_order_id',
            'customer_id'
        );
    }

    /** Client name via the linked sales order. */
    public function getClientNameAttribute(): string
    {
        return $this->salesOrder?->customer?->name ?? 'N/A';
    }

    /** Invoice number of the linked invoice (fallback to sales-order PO). */
    public function getInvoiceNumberAttribute(): ?string
    {
        return $this->invoice?->invoice_number;
    }

    public function getInvoiceDateAttribute(): ?string
    {
        return $this->invoice?->invoice_date?->format('d-m-Y');
    }
}
