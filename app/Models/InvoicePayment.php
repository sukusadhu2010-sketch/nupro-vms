<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_mode',
        'upi_id',
        'cheque_number',
        'bank_details',
        'beneficiary_details',
        'transaction_id',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the invoice that this payment belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get formatted payment details.
     */
    public function getDetailsAttribute(): string
    {
        $details = [];
        if ($this->payment_mode === 'upi' && $this->upi_id) {
            $details[] = 'UPI: ' . $this->upi_id;
        }
        if ($this->payment_mode === 'cheque') {
            if ($this->cheque_number) $details[] = 'Chq: ' . $this->cheque_number;
            if ($this->bank_details) $details[] = $this->bank_details;
        }
        if ($this->payment_mode === 'fund_transfer') {
            if ($this->beneficiary_details) $details[] = $this->beneficiary_details;
            if ($this->transaction_id) $details[] = 'TXN: ' . $this->transaction_id;
        }
        return implode(', ', $details);
    }

    /**
     * Get payment mode label.
     */
    public function getModeLabelAttribute(): string
    {
        return match ($this->payment_mode) {
            'cash' => 'Cash',
            'fund_transfer' => 'Fund Transfer',
            'cheque' => 'Cheque',
            'upi' => 'UPI',
            default => ucfirst($this->payment_mode),
        };
    }
}

