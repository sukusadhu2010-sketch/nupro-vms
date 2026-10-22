<?php

namespace App\Observers;

use App\Models\SalesOrder;

class SalesOrderObserver
{
    /**
     * Handle the SalesOrder "updated" event.
     */
    public function updated(SalesOrder $salesOrder): void
    {
        if ($salesOrder->isDirty('status') && $salesOrder->status === 'confirmed') {
            foreach ($salesOrder->items as $item) {
                $product = $item->product;

                if ($product) {
                    // stock_quantity is UNSIGNED — a plain decrement (or even
                    // GREATEST(stock_quantity - x, 0)) throws "BIGINT UNSIGNED value
                    // is out of range" when stock < x, because MySQL evaluates the
                    // subtraction in unsigned arithmetic before GREATEST clamps it.
                    // Use CASE WHEN so the subtraction only runs when stock >= qty.
                    \Illuminate\Support\Facades\DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'stock_quantity' => \Illuminate\Support\Facades\DB::raw(
                                'CASE WHEN stock_quantity >= ' . (float) $item->quantity .
                                ' THEN stock_quantity - ' . (float) $item->quantity . ' ELSE 0 END'
                            ),
                            'updated_at'     => now(),
                        ]);

                    // Keep the in-memory model in sync without firing another query
                    $product->stock_quantity = max(0, (float) $product->stock_quantity - (float) $item->quantity);
                }
            }
        }
    }
}

