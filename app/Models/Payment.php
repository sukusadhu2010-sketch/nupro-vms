<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    public const DIRECTION_RECEIPT = 'receipt';
    public const DIRECTION_PAYMENT = 'payment';
    public const DIRECTION_RECEIVED = 'receipt';
    public const DIRECTION_PAID = 'payment';

    public const MODES = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque' => 'Cheque',
        'neft' => 'NEFT',
        'rtgs' => 'RTGS',
        'upi' => 'UPI',
    ];

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
        return ($this->direction === self::DIRECTION_RECEIPT || $this->direction === 'received')
            ? ($this->customer?->name ?? '-')
            : ($this->vendor?->name ?? '-');
    }

    public function isReceipt(): bool
    {
        return $this->direction === self::DIRECTION_RECEIPT || $this->direction === 'received';
    }

    public function isPayment(): bool
    {
        return $this->direction === self::DIRECTION_PAYMENT || $this->direction === 'paid';
    }

    /**
     * Generate next payment/receipt number, e.g. RCPT/2026-27/00001 or PYMT/2026-27/00001
     */
    public static function generatePaymentNumber(string $direction = self::DIRECTION_RECEIPT): string
    {
        $fy = FinancialYear::active();
        $fyLabel = $fy ? $fy->fy_label : (date('Y') . '-' . substr((string)(date('Y') + 1), -2));

        $prefix = ($direction === self::DIRECTION_RECEIPT || $direction === 'received') ? 'RCPT' : 'PYMT';
        $prefixPattern = "{$prefix}/{$fyLabel}/";

        $last = static::where('payment_number', 'like', "{$prefixPattern}%")
            ->orderByDesc('id')
            ->first();

        $seq = 1;
        if ($last) {
            $parts = explode('/', $last->payment_number);
            $lastSeq = (int) end($parts);
            $seq = $lastSeq + 1;
        }

        return sprintf('%s/%s/%05d', $prefix, $fyLabel, $seq);
    }
}
