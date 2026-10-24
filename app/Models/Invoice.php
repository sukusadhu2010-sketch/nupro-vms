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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

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
    public function recalcTotals(bool $isInterstate = false): void
    {
        $items = $this->items()->get();

        $subTotal = (float) $items->sum(fn ($i) => (float) $i->quantity * (float) $i->rate);
        $discount = (float) $items->sum(fn ($i) => (float) $i->discount_amount);
        $taxable = max(0, $subTotal - $discount);
        $totalTax = (float) $items->sum(fn ($i) => (float) $i->tax_amount);

        // Check if customer state differs from organization setting
        $org = OrganizationSetting::current();
        if ($this->customer && $this->customer->state && $org->state) {
            $isInterstate = strcasecmp(trim($this->customer->state), trim($org->state)) !== 0;
        }

        $cgst = $isInterstate ? 0.00 : round($totalTax / 2, 2);
        $sgst = $isInterstate ? 0.00 : round($totalTax / 2, 2);
        $igst = $isInterstate ? round($totalTax, 2) : 0.00;

        $computedTotal = $taxable + $cgst + $sgst + $igst + (float) $this->cess + (float) $this->tcs + (float) $this->shipping_charge;
        $rounded = round($computedTotal);
        $roundOff = round($rounded - $computedTotal, 2);

        $this->update([
            'sub_total' => $subTotal,
            'discount_amount' => $discount,
            'taxable_amount' => $taxable,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'round_off' => $roundOff,
            'grand_total' => $rounded,
        ]);
    }

    /**
     * Generate next invoice number, e.g. INV/2026-27/00001
     */
    public static function generateInvoiceNumber(?string $type = 'tax'): string
    {
        $fy = FinancialYear::active();
        $fyLabel = $fy ? $fy->fy_label : (date('Y') . '-' . substr((string)(date('Y') + 1), -2));

        $prefix = match ($type) {
            'proforma' => 'PI',
            'commercial' => 'CI',
            default => 'INV',
        };

        $prefixPattern = "{$prefix}/{$fyLabel}/";
        $lastInvoice = static::where('invoice_number', 'like', "{$prefixPattern}%")
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($lastInvoice) {
            $parts = explode('/', $lastInvoice->invoice_number);
            $lastSeq = (int) end($parts);
            $seq = $lastSeq + 1;
        }

        return sprintf('%s/%s/%05d', $prefix, $fyLabel, $seq);
    }
}
