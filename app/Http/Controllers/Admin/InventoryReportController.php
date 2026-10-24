<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLedger;
use App\Models\Procurement;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    /**
     * Report 1: Inventory Ledger General Report
     */
    public function inventoryLedger(Request $request)
    {
        $query = InventoryLedger::with(['product.category', 'vendor', 'purchaseOrder'])->latest('transaction_date');

        if ($request->filled('date_from')) $query->whereDate('transaction_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('transaction_date', '<=', $request->date_to);
        if ($request->filled('product_id')) $query->where('product_id', $request->product_id);
        if ($request->filled('transaction_type')) $query->where('transaction_type', $request->transaction_type);

        $ledgers = $query->paginate(25)->withQueryString();
        $products = Product::orderBy('name')->get(['id', 'name']);

        return view('admin.reports.inventory.ledger', compact('ledgers', 'products'));
    }

    /**
     * Report 2: Stock Movement Report (Summary of In / Out per product)
     */
    public function stockMovement(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $movements = Product::with(['category', 'unit'])
            ->get()
            ->map(function ($product) use ($dateFrom, $dateTo) {
                // Opening balance before dateFrom
                $openingIn = (float) InventoryLedger::where('product_id', $product->id)
                    ->whereDate('transaction_date', '<', $dateFrom)
                    ->sum('qty_in');
                $openingOut = (float) InventoryLedger::where('product_id', $product->id)
                    ->whereDate('transaction_date', '<', $dateFrom)
                    ->sum('qty_out');
                $openingBalance = $openingIn - $openingOut;

                // Movements during period
                $qtyIn = (float) InventoryLedger::where('product_id', $product->id)
                    ->whereDate('transaction_date', '>=', $dateFrom)
                    ->whereDate('transaction_date', '<=', $dateTo)
                    ->sum('qty_in');
                $qtyOut = (float) InventoryLedger::where('product_id', $product->id)
                    ->whereDate('transaction_date', '>=', $dateFrom)
                    ->whereDate('transaction_date', '<=', $dateTo)
                    ->sum('qty_out');

                $closingBalance = $openingBalance + $qtyIn - $qtyOut;

                return [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category?->name ?? '—',
                    'unit' => $product->unit?->short_name ?? 'Nos',
                    'opening' => $openingBalance,
                    'qty_in' => $qtyIn,
                    'qty_out' => $qtyOut,
                    'closing' => $closingBalance,
                ];
            });

        return view('admin.reports.inventory.stock-movement', compact('movements', 'dateFrom', 'dateTo'));
    }

    /**
     * Report 3: PO Inventory Report (PO Qty, Received, Pending, Consumed, Available)
     */
    public function poInventoryReport(Request $request)
    {
        $query = Procurement::with(['vendor', 'items.product'])->latest('order_date');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $pos = $query->paginate(20)->withQueryString();

        $rows = [];
        foreach ($pos as $po) {
            foreach ($po->items as $item) {
                $ordered = (float) $item->quantity;
                $received = (float) $item->received_quantity;
                $pending = max(0, $ordered - $received);

                $consumed = (float) InventoryLedger::where('purchase_order_id', $po->id)
                    ->where('product_id', $item->product_id)
                    ->where('transaction_type', 'issue')
                    ->sum('qty_out');

                $available = max(0, $received - $consumed);

                $rows[] = [
                    'po_number' => $po->po_number,
                    'po_date' => $po->order_date?->format('d/m/Y'),
                    'vendor' => $po->vendor?->name ?? '—',
                    'product' => $item->product?->name ?? '—',
                    'sku' => $item->product?->sku ?? '—',
                    'po_qty' => $ordered,
                    'received_qty' => $received,
                    'pending_qty' => $pending,
                    'consumed_qty' => $consumed,
                    'available_qty' => $available,
                ];
            }
        }

        return view('admin.reports.inventory.po-report', compact('rows', 'pos'));
    }

    /**
     * Report 4: Item Ledger Report (Product-wise historical drill-down)
     */
    public function itemLedgerReport(Request $request)
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);
        $selectedProduct = null;
        $ledgerEntries = collect();
        $summary = ['total_in' => 0, 'total_out' => 0, 'current_balance' => 0];

        if ($request->filled('product_id')) {
            $selectedProduct = Product::with(['category', 'unit'])->find($request->product_id);

            if ($selectedProduct) {
                $ledgerEntries = InventoryLedger::with(['vendor', 'purchaseOrder'])
                    ->where('product_id', $selectedProduct->id)
                    ->orderBy('transaction_date')
                    ->orderBy('id')
                    ->get();

                $summary['total_in'] = $ledgerEntries->sum('qty_in');
                $summary['total_out'] = $ledgerEntries->sum('qty_out');
                $summary['current_balance'] = $summary['total_in'] - $summary['total_out'];
            }
        }

        return view('admin.reports.inventory.item-ledger', compact('products', 'selectedProduct', 'ledgerEntries', 'summary'));
    }
}

