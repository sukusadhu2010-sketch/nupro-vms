<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryLedger extends Model
{
    protected $table = 'inventory_ledgers';

    protected $fillable = [
        'transaction_date',
        'transaction_type',
        'reference_no',
        'purchase_order_id',
        'vendor_id',
        'product_id',
        'warehouse_id',
        'qty_in',
        'qty_out',
        'balance_qty',
        'unit',
        'rate',
        'remarks',
        'created_by',
        'source_id',
        'source_type',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'qty_in' => 'decimal:3',
        'qty_out' => 'decimal:3',
        'balance_qty' => 'decimal:3',
        'rate' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(Procurement::class, 'purchase_order_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Running balance for a product/warehouse after this entry.
     * Calculated as sum of qty_in - qty_out up to and including this row.
     */
    public static function nextBalance(int $productId, ?int $warehouseId = null): float
    {
        $q = static::where('product_id', $productId);
        if ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        }
        return (float) $q->sum('qty_in') - (float) $q->sum('qty_out');
    }
}
