<?php

namespace App\Services;

use App\Events\SalesOrderCreated;
use App\Exceptions\QuotationAlreadyConvertedException;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Support\Facades\DB;
use Throwable;

class SalesOrderService
{
    /**
     * Convert an accepted quotation into a sales order.
     *
     * @throws QuotationAlreadyConvertedException
     * @throws Throwable
     */
    public function convertToOrder(Quotation $quotation, array $extra = []): SalesOrder
    {
        if ($quotation->status === 'converted') {
            throw new QuotationAlreadyConvertedException();
        }

        return DB::transaction(function () use ($quotation, $extra) {
            $enquiry = $quotation->enquiry;
            $customer = $enquiry->customer;

            $orderNumber = 'SO-' . date('Y') . '-' . str_pad($quotation->id, 4, '0', STR_PAD_LEFT);

            $salesOrder = SalesOrder::create([
                'quotation_id' => $quotation->id,
                'customer_id' => $customer->id,
                'order_number' => $orderNumber,
                'po_number' => $extra['po_number'] ?? null,
                'job_number' => $extra['job_number'] ?? null,
                'status' => 'draft',
                'total_amount' => $quotation->total_amount,
                'shipping_address' => $customer->address ?? null,
                'expected_delivery_date' => now()->addDays(7)->toDateString(),
            ]);

            foreach ($quotation->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->total_price,
                ]);
            }

            $quotation->update(['status' => 'converted']);

            SalesOrderCreated::dispatch($salesOrder);

            return $salesOrder;
        });
    }
}

