<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'enquiry_id',
        'quote_number',
        'version',
        'status',
        'total_amount',
        'notes',
        'attachments',
        'valid_until',
        'tax_type',
        'igst',
        'sgst',
        'cgst',
        'tax_amount',
        'amount_in_words',
        // Acknowledgement & Product Specification
        'acknowledgement',
        'product_spec',
        // Terms & Conditions
        'delivery_terms',
        'warranty_terms',
        'payment_terms',
        'inspection_vendor_scope',
        'inspection_third_party_scope',
        // Notes & Signatory
        'closing_statement',
        'signatory_company',
        'signatory_designation',
    ];

    protected static $statuses = ['draft', 'sent', 'accepted', 'expired', 'revised', 'converted'];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'igst' => 'decimal:2',
        'sgst' => 'decimal:2',
        'cgst' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'valid_until' => 'date',
        'attachments' => 'array',
    ];

    /** Grand total = quantity amount + tax amount */
    public function getGrandTotalAttribute(): float
    {
        return round(((float) $this->total_amount) + ((float) $this->tax_amount), 2);
    }

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function salesOrder()
    {
        return $this->hasOne(SalesOrder::class);
    }

    public function getStatusBadgeAttribute()
    {
        $config = [
            'draft' => 'bg-secondary',
            'sent' => 'bg-info',
            'accepted' => 'bg-success',
            'expired' => 'bg-warning',
            'revised' => 'bg-dark',
            'converted' => 'bg-primary',
        ];
        return $config[$this->status] ?? 'bg-secondary';
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }

    public function getIsActiveAttribute()
    {
        return in_array($this->status, ['draft', 'sent']);
    }

    public function getIsAcceptedAttribute()
    {
        return $this->status === 'accepted';
    }

    public function getFormattedQuoteNumberAttribute()
    {
        return $this->quote_number . ' (V' . $this->version . ')';
    }
}

