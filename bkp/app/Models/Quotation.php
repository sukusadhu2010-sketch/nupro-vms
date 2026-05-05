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
        'valid_until',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'valid_until' => 'date',
    ];

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function getStatusBadgeAttribute()
    {
        $config = [
            'draft' => 'bg-secondary',
            'sent' => 'bg-info',
            'accepted' => 'bg-success',
            'expired' => 'bg-warning',
            'revised' => 'bg-dark',
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

