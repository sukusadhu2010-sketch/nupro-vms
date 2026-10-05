<?php

namespace App\Services;

use App\Events\SalesOrderCreated;
use App\Exceptions\QuotationAlreadyConvertedException;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Support\Facades\DB;
use App\Services\JobNumberService;
use Throwable;

class SalesOrderService
{
    public function __construct(private readonly JobNumberService $jobNumberService)
    {
    }

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
                'job_number' => $extra['job_number'] ?? $this->jobNumberService->generate(),
                'customer_po_number' => $extra['customer_po_number'] ?? null,
                'customer_po_date' => $extra['customer_po_date'] ?? null,
                'mtc' => $extra['mtc'] ?? false,
                'pdi' => $extra['pdi'] ?? false,
                'sd_pbg' => $extra['sd_pbg'] ?? false,
                'delivery_target_date' => $extra['delivery_target_date'] ?? null,
                'payment_mode' => $extra['payment_mode'] ?? null,
                'credit_days' => $extra['credit_days'] ?? null,
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

