<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcurementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_id',
        'sales_order_item_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'received_quantity',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ProcurementReceipt::class)->orderBy('received_at', 'desc');
    }

    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }

    public function getPendingQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->received_quantity);
    }

    protected static function booted(): void
    {
        static::saving(function ($item) {
            $item->subtotal = $item->quantity * $item->unit_price;
        });
    }
}

