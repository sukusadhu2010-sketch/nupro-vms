<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'product_id',
        'transaction_type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'reference_type',
        'reference_id',
        'transaction_date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_quantity' => 'integer',
        'new_quantity' => 'integer',
        'transaction_date' => 'date',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for inflow transactions (from procurement).
     */
    public function scopeInflow($query)
    {
        return $query->where('transaction_type', 'inflow');
    }

    /**
     * Scope for outflow transactions (to sales order).
     */
    public function scopeOutflow($query)
    {
        return $query->where('transaction_type', 'outflow');
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope for specific date.
     */
    public function scopeDate($query, $date)
    {
        return $query->whereDate('transaction_date', $date);
    }

    /**
     * Get reference URL.
     */
    public function getReferenceUrlAttribute(): ?string
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        return match ($this->reference_type) {
            'Procurement' => route('procurements.show', $this->reference_id),
            'SalesOrder' => route('sales-orders.show', $this->reference_id),
            default => null,
        };
    }

    /**
     * Get reference label.
     */
    public function getReferenceLabelAttribute(): string
    {
        if (!$this->reference_type || !$this->reference_id) {
            return 'N/A';
        }

        return match ($this->reference_type) {
            'Procurement' => 'PO: ' . ($this->reference->po_number ?? $this->reference_id),
            'SalesOrder' => 'SO: ' . ($this->reference->order_number ?? $this->reference_id),
            default => $this->reference_type . ' #' . $this->reference_id,
        };
    }
}
