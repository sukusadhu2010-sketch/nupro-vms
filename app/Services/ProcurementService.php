<?php

namespace App\Services;

use App\Models\Procurement;
use App\Models\ProcurementItem;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcurementService
{
    /**
     * Generate vendor-wise procurement orders from a confirmed sales order.
     *
     * @return array<int, Procurement>
     *
     * @throws Throwable
     */
    public function generateFromSalesOrder(SalesOrder $salesOrder, array $extra = []): array
    {
        // Ensure sales order is in a state that can be procured
        if (! in_array($salesOrder->status, ['confirmed', 'processing'])) {
            throw new \InvalidArgumentException('Sales order must be confirmed or processing to generate procurements.');
        }

        return DB::transaction(function () use ($salesOrder, $extra) {
            $salesOrder->load(['items.product.vendor']);

            // Group items by vendor
            $vendorGroups = [];
            foreach ($salesOrder->items as $item) {
                $vendor = $item->product?->vendor;
                if (! $vendor) {
                    continue; // Skip items without a vendor
                }
                $vendorGroups[$vendor->id]['vendor'] = $vendor;
                $vendorGroups[$vendor->id]['items'][] = $item;
            }

            $procurements = [];
            $now = now();

            foreach ($vendorGroups as $vendorId => $group) {
                $vendor = $group['vendor'];
                $items = $group['items'];

                $totalAmount = collect($items)->sum(fn ($i) => $i->quantity * $i->unit_price);
                $poNumber = $this->generatePoNumber($salesOrder, $vendorId);

                $procurement = Procurement::create([
                    'sales_order_id' => $salesOrder->id,
                    'vendor_id' => $vendor->id,
                    'po_number' => $poNumber,
                    'status' => 'draft',
                    'total_amount' => $totalAmount,
                    'order_date' => $extra['order_date'] ?? $now->toDateString(),
                    'expected_delivery_date' => $extra['expected_delivery_date'] ?? $now->copy()->addDays(14)->toDateString(),
                    'shipping_address' => $extra['shipping_address'] ?? $salesOrder->shipping_address,
                    'notes' => $extra['notes'] ?? null,
                ]);

                foreach ($items as $item) {
                    ProcurementItem::create([
                        'procurement_id' => $procurement->id,
                        'sales_order_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                        'received_quantity' => 0,
                        'notes' => null,
                    ]);
                }

                $procurements[] = $procurement;
            }

            return $procurements;
        });
    }

    /**
     * Generate a unique PO number.
     */
    private function generatePoNumber(SalesOrder $salesOrder, int $vendorId): string
    {
        $prefix = 'PO';
        $soPart = str_pad((string) $salesOrder->id, 4, '0', STR_PAD_LEFT);
        $vendorPart = str_pad((string) $vendorId, 3, '0', STR_PAD_LEFT);
        $random = strtoupper(substr(uniqid(), -4));

        return "{$prefix}-{$soPart}-{$vendorPart}-{$random}";
    }
}

