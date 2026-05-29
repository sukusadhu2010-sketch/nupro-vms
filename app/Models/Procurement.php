<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Procurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'vendor_id',
        'po_number',
        'status',
        'total_amount',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'shipping_address',
        'notes',
        'attachments',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'attachments' => 'array',
    ];

    /**
     * Get multiple sales orders linked to this procurement.
     */
    public function salesOrders(): BelongsToMany
    {
        return $this->belongsToMany(SalesOrder::class, 'procurement_sales_order')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Get the primary sales order (for backward compatibility).
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProcurementItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ProcurementReceipt::class)->orderBy('received_at', 'desc');
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->where('transaction_type', 'expense')->sum('amount');
    }

    public function getOutstandingAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function getStatusBadgeAttribute(): string
    {
        $config = [
            'draft' => 'bg-secondary',
            'sent' => 'bg-info',
            'partially_received' => 'bg-warning',
            'received' => 'bg-success',
            'cancelled' => 'bg-danger',
        ];

        return $config[$this->status] ?? 'bg-secondary';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'partially_received' => 'Partially Received',
            default => ucfirst($this->status),
        };
    }

    public function getIsFullyReceivedAttribute(): bool
    {
        if ($this->items->isEmpty()) {
            return false;
        }

        return $this->items->every(fn ($item) => $item->received_quantity >= $item->quantity);
    }
}
