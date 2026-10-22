<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'type',
        'sales_order_id',
        'purchase_order_id',
        'delivery_id',
        'customer_id',
        'vendor_id',
        'invoice_date',
        'po_number',
        'po_date',
        'billing_address',
        'shipping_address',
        'customer_gst',
        'sub_total',
        'discount_amount',
        'taxable_amount',
        'cgst',
        'sgst',
        'igst',
        'cess',
        'tcs',
        'shipping_charge',
        'round_off',
        'grand_total',
        'paid_amount',
        'status',
        'terms',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'po_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'cgst' => 'decimal:2',
        'sgst' => 'decimal:2',
        'igst' => 'decimal:2',
        'cess' => 'decimal:2',
        'tcs' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'round_off' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(Procurement::class, 'purchase_order_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function getOutstandingAttribute(): float
    {
        return max(0, (float) $this->grand_total - (float) $this->paid_amount);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-secondary',
            'pending' => 'bg-warning',
            'approved' => 'bg-info',
            'paid' => 'bg-success',
            'partially_paid' => 'bg-primary',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'tax' => 'Tax Invoice',
            'proforma' => 'Proforma Invoice',
            'commercial' => 'Commercial Invoice',
            default => ucfirst($this->type),
        };
    }

    /**
     * Recalculate header totals from items.
     */
    public function recalcTotals(): void
    {
        $items = $this->items()->get();

        $subTotal = $items->sum(fn ($i) => (float) $i->quantity * (float) $i->rate);
        $discount = $items->sum(fn ($i) => (float) $i->discount_amount);
        $taxable = $subTotal - $discount;
        // tax_percent on each item is the combined rate; split CGST/SGST 50/50
        $tax = $items->sum(fn ($i) => (float) $i->tax_amount);

        $this->update([
            'sub_total' => $subTotal,
            'discount_amount' => $discount,
            'taxable_amount' => $taxable,
            'cgst' => $tax / 2,
            'sgst' => $tax / 2,
            'grand_total' => round($taxable + $tax + (float) $this->cess + (float) $this->tcs + (float) $this->shipping_charge, 2),
        ]);
    }
}
