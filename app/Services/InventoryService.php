<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Procurement;
use App\Models\ProcurementItem;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService
{
/**
     * Add stock from a procurement (when items are received).
     * This handles both full and partial receipt scenarios.
     */
    public function addStockFromProcurement(Procurement $procurement, ?string $notes = null): void
    {
        $procurement->load('items.product');
        
        DB::transaction(function () use ($procurement, $notes) {
            foreach ($procurement->items as $item) {
                // Use received_quantity if available (for partial receives)
                // Otherwise use quantity (for full receives)
                $quantityToAdd = $item->received_quantity > 0 ? $item->received_quantity : $item->quantity;
                
                if ($quantityToAdd > 0) {
                    $this->addStock(
                        productId: $item->product_id,
                        quantity: $quantityToAdd,
                        transactionDate: $procurement->actual_delivery_date ?? now()->toDateString(),
                        referenceType: 'Procurement',
                        referenceId: $procurement->id,
                        notes: $notes ?? "Stock received from PO: {$procurement->po_number}"
                    );
                }
            }
        });
    }

    /**
     * Add stock for a specific product.
     */
    public function addStock(
        int $productId,
        int $quantity,
        string $transactionDate,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): InventoryTransaction {
        $inventory = Inventory::firstOrCreate(
            ['product_id' => $productId],
            ['quantity' => 0, 'reserved_quantity' => 0, 'reorder_level' => 0]
        );

        $previousQuantity = $inventory->quantity;
        $newQuantity = $previousQuantity + $quantity;

        // Update inventory
        $inventory->update([
            'quantity' => $newQuantity,
            'last_transaction_date' => $transactionDate,
        ]);

        // Record transaction
        $transaction = InventoryTransaction::create([
            'inventory_id' => $inventory->id,
            'product_id' => $productId,
            'transaction_type' => 'inflow',
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'transaction_date' => $transactionDate,
            'notes' => $notes,
            'user_id' => Auth::id(),
        ]);

        // Also update product stock_quantity for backward compatibility
        Product::where('id', $productId)->update(['stock_quantity' => $newQuantity]);

        return $transaction;
    }

    /**
     * Deduct stock for a sales order (when delivered).
     */
    public function deductStockFromSalesOrder(SalesOrder $salesOrder, ?string $notes = null): void
    {
        $salesOrder->load('items.product');
        
        DB::transaction(function () use ($salesOrder, $notes) {
            foreach ($salesOrder->items as $item) {
                $this->deductStock(
                    productId: $item->product_id,
                    quantity: $item->quantity,
                    transactionDate: $salesOrder->expected_delivery_date ?? now()->toDateString(),
                    referenceType: 'SalesOrder',
                    referenceId: $salesOrder->id,
                    notes: $notes ?? "Stock deducted for Order: {$salesOrder->order_number}"
                );
            }
        });
    }

    /**
     * Deduct stock for a specific product.
     */
    public function deductStock(
        int $productId,
        int $quantity,
        string $transactionDate,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): ?InventoryTransaction {
        $inventory = Inventory::firstOrCreate(
            ['product_id' => $productId],
            ['quantity' => 0, 'reserved_quantity' => 0, 'reorder_level' => 0]
        );

        // Check if sufficient stock available
        if ($inventory->quantity < $quantity) {
            throw new \RuntimeException(
                "Insufficient stock for product ID {$productId}. Available: {$inventory->quantity}, Requested: {$quantity}"
            );
        }

        $previousQuantity = $inventory->quantity;
        $newQuantity = $previousQuantity - $quantity;

        // Update inventory
        $inventory->update([
            'quantity' => $newQuantity,
            'last_transaction_date' => $transactionDate,
        ]);

        // Record transaction
        $transaction = InventoryTransaction::create([
            'inventory_id' => $inventory->id,
            'product_id' => $productId,
            'transaction_type' => 'outflow',
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'transaction_date' => $transactionDate,
            'notes' => $notes,
            'user_id' => Auth::id(),
        ]);

        // Also update product stock_quantity for backward compatibility
        Product::where('id', $productId)->update(['stock_quantity' => $newQuantity]);

        return $transaction;
    }

    /**
     * Get inventory for a specific product.
     */
    public function getInventoryForProduct(int $productId): ?Inventory
    {
        return Inventory::where('product_id', $productId)->first();
    }

    /**
     * Get all inventories with optional date filtering.
     */
    public function getAllInventories(?string $startDate = null, ?string $endDate = null)
    {
        $query = Inventory::with('product');
        
        if ($startDate && $endDate) {
            $query->whereHas('transactions', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('transaction_date', [$startDate, $endDate]);
            });
        }
        
        return $query->orderBy('quantity', 'asc')->get();
    }

    /**
     * Get inventory transactions by date range.
     */
    public function getTransactionsByDate(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $type = null
    ) {
        $query = InventoryTransaction::with(['product', 'user', 'inventory']);
        
        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }
        
        if ($type) {
            $query->where('transaction_type', $type);
        }
        
        return $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get transaction summary by date.
     */
    public function getSummaryByDate(?string $date = null): array
    {
        $query = InventoryTransaction::with('product');
        
        if ($date) {
            $query->whereDate('transaction_date', $date);
        }
        
        $transactions = $query->get();
        
        $inflow = $transactions->where('transaction_type', 'inflow')->sum('quantity');
        $outflow = $transactions->where('transaction_type', 'outflow')->sum('quantity');
        
        return [
            'date' => $date ?? now()->toDateString(),
            'inflow' => $inflow,
            'outflow' => $outflow,
            'net' => $inflow - $outflow,
            'transaction_count' => $transactions->count(),
        ];
    }

    /**
     * Get products that need reordering.
     */
    public function getProductsNeedingReorder(): \Illuminate\Database\Eloquent\Collection
    {
        return Inventory::whereRaw('quantity <= reorder_level')
            ->with('product')
            ->get();
    }

    /**
     * Get inventory value summary.
     */
    public function getInventoryValue(): array
    {
        $inventories = Inventory::with('product')->get();
        
        $totalQuantity = $inventories->sum('quantity');
        $totalValue = $inventories->reduce(function ($carry, $inv) {
            return $carry + ($inv->quantity * ($inv->product->price ?? 0));
        }, 0);
        
        return [
            'total_quantity' => $totalQuantity,
            'total_value' => $totalValue,
            'product_count' => $inventories->count(),
        ];
    }
}
