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
                    $product->decrement('stock_quantity', $item->quantity);
                }
            }
        }
    }
}

