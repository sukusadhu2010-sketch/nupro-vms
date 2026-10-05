<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'customer_id',
        'order_number',
        'customer_po_number',
        'customer_po_date',
        'mtc',
        'pdi',
        'sd_pbg',
        'delivery_target_date',
        'payment_mode',
        'credit_days',
        'job_number',
        'status',
        'total_amount',
        'shipping_address',
        'expected_delivery_date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'expected_delivery_date' => 'date',
        'customer_po_date' => 'date',
        'delivery_target_date' => 'date',
        'mtc' => 'boolean',
        'pdi' => 'boolean',
        'sd_pbg' => 'boolean',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    /**
     * Get multiple procurements linked to this sales order.
     */
    public function procurements(): BelongsToMany
    {
        return $this->belongsToMany(Procurement::class, 'procurement_sales_order')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Get the primary procurement (for backward compatibility).
     */
    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function getStatusBadgeAttribute(): string
    {
        $config = [
            'draft' => 'bg-secondary',
            'confirmed' => 'bg-success',
            'processing' => 'bg-info',
            'shipped' => 'bg-primary',
            'delivered' => 'bg-dark',
            'cancelled' => 'bg-danger',
        ];

        return $config[$this->status] ?? 'bg-secondary';
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    public const PAYMENT_MODES = ['lc', 'credit', 'advance_pi', 'proforma_invoice'];

    public const PAYMENT_MODE_LABELS = [
        'lc' => 'LC (Letter of Credit)',
        'credit' => 'Credit',
        'advance_pi' => 'Advance + PI',
        'proforma_invoice' => 'Proforma Invoice',
    ];

    public function getPaymentModeLabelAttribute(): string
    {
        return self::PAYMENT_MODE_LABELS[$this->payment_mode] ?? '—';
    }

    /**
     * Whether the payment mode requires a credit-days value.
     */
    public function isCreditDaysRequired(): bool
    {
        return in_array($this->payment_mode, ['lc', 'credit'], true);
    }

    public function getPaymentModeLabelWithDaysAttribute(): string
    {
        $label = $this->payment_mode_label;

        if ($this->credit_days !== null) {
            $label .= ' (' . $this->credit_days . ' days)';
        }

        return $label;
    }
}

