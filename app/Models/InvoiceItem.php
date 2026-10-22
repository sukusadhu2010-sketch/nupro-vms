<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'invoiceable_id',
        'invoiceable_type',
        'item_name',
        'description',
        'hsn_code',
        'quantity',
        'unit',
        'rate',
        'discount_percent',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'rate' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Compute the line amount from qty x rate - discount + tax.
     */
    public function compute(): void
    {
        $gross = (float) $this->quantity * (float) $this->rate;
        $discount = $this->discount_percent > 0
            ? $gross * ((float) $this->discount_percent / 100)
            : (float) $this->discount_amount;
        $this->discount_amount = round($discount, 2);

        $taxable = $gross - $discount;
        $this->tax_amount = round($taxable * ((float) $this->tax_percent / 100), 2);
        $this->amount = round($taxable + $this->tax_amount, 2);
    }
}
