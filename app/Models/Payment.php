<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Procurement;
use App\Models\Invoice;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payable_id',
        'payable_type',
        'transaction_type',
        'contact_id',
        'contact_type',
        'amount',
        'payment_mode',
        'payment_date',
        'upi_id',
        'cheque_number',
        'cheque_date',
        'bank_name',
        'beneficiary_details',
        'transaction_id',
        'ledger_account_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'cheque_date' => 'date',
    ];

    public function payable()
    {
        return $this->morphTo();
    }

    public function contactable()
    {
        return $this->morphTo('contact', 'contact_type', 'contact_id');
    }

    public function ledgerAccount()
    {
        return $this->belongsTo(Ledger::class, 'ledger_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors for display
    public function getModeLabelAttribute(): string
    {
        return match ($this->payment_mode) {
            'cash' => 'Cash',
            'fund_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'upi' => 'UPI',
            default => ucfirst($this->payment_mode),
        };
    }

    public function getDetailsAttribute(): string
    {
        $details = [];
        if ($this->payment_mode === 'upi' && $this->upi_id) {
            $details[] = 'UPI: ' . $this->upi_id;
        }
        if ($this->payment_mode === 'cheque') {
            if ($this->cheque_number) $details[] = 'Chq No: ' . $this->cheque_number;
            if ($this->cheque_date) $details[] = 'Date: ' . $this->cheque_date->format('d M Y');
            if ($this->bank_name) $details[] = $this->bank_name;
        }
        if ($this->payment_mode === 'fund_transfer') {
            if ($this->beneficiary_details) $details[] = $this->beneficiary_details;
            if ($this->transaction_id) $details[] = 'TXN: ' . $this->transaction_id;
        }
        return implode(', ', $details);
    }
}
