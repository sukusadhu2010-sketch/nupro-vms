<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'reserved_quantity',
        'reorder_level',
        'last_transaction_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'reorder_level' => 'integer',
        'last_transaction_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class)->orderBy('transaction_date', 'desc');
    }

    /**
     * Get available quantity (excluding reserved).
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /**
     * Check if reorder is needed.
     */
    public function getNeedsReorderAttribute(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    /**
     * Get transactions by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereHas('transactions', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('transaction_date', [$startDate, $endDate]);
        });
    }

    /**
     * Get transactions by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->whereHas('transactions', function ($q) use ($type) {
            $q->where('transaction_type', $type);
        });
    }
}
