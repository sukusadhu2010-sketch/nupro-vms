<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\InventoryLedger;
use App\Models\Procurement;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryLedgerController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryLedger::with(['product.category', 'vendor', 'purchaseOrder'])
            ->latest('transaction_date')
            ->latest('id');

        // Filters
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('purchase_order_id')) {
            $query->where('purchase_order_id', $request->purchase_order_id);
        }
        if ($request->filled('category_id')) {
            $query->whereHas('product', fn ($pq) => $pq->where('product_category_id', $request->category_id));
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
                  ->orWhereHas('purchaseOrder', fn ($poq) => $poq->where('po_number', 'like', "%{$search}%"));
            });
        }

        $ledgers = $query->paginate(20)->withQueryString();

        $metrics = [
            'total_qty_in' => InventoryLedger::sum('qty_in'),
            'total_qty_out' => InventoryLedger::sum('qty_out'),
            'current_stock_units' => DB::table('stock_balances')->sum('quantity'),
            'tracked_items_count' => DB::table('stock_balances')->where('quantity', '>', 0)->count(),
        ];

        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);
        $vendors = Vendor::orderBy('name')->get(['id', 'name']);
        $categories = ProductCategory::orderBy('name')->get(['id', 'name']);
        $purchaseOrders = Procurement::orderByDesc('order_date')->get(['id', 'po_number']);

        return view('admin.inventory.index', compact(
            'ledgers',
            'metrics',
            'products',
            'vendors',
            'categories',
            'purchaseOrders'
        ));
    }

    public function create(Request $request)
    {
        $products = Product::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        $purchaseOrders = Procurement::with('vendor')->latest()->get();

        return view('admin.inventory.create', compact('products', 'vendors', 'purchaseOrders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:po,grn,issue,transfer,adjustment',
            'product_id' => 'required|exists:products,id',
            'reference_no' => 'nullable|string|max:100',
            'purchase_order_id' => 'nullable|exists:procurements,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'warehouse_id' => 'nullable|integer',
            'quantity' => 'required|numeric|min:0.001',
            'unit' => 'nullable|string|max:20',
            'remarks' => 'nullable|string',
            'allow_negative' => 'nullable|boolean',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $unit = $validated['unit'] ?: ($product->unit?->short_name ?? 'Nos');
        $qty = (float) $validated['quantity'];

        // Determine qty_in vs qty_out based on transaction type
        $qtyIn = 0;
        $qtyOut = 0;
        if (in_array($validated['transaction_type'], ['grn', 'po'])) {
            $qtyIn = $qty;
        } elseif (in_array($validated['transaction_type'], ['issue', 'transfer'])) {
            $qtyOut = $qty;
        } elseif ($validated['transaction_type'] === 'adjustment') {
            if ($request->input('adjustment_direction') === 'subtract') {
                $qtyOut = $qty;
            } else {
                $qtyIn = $qty;
            }
        }

        try {
            $ledger = InventoryLedger::recordMovement([
                'transaction_date' => $validated['transaction_date'],
                'transaction_type' => $validated['transaction_type'],
                'reference_no' => $validated['reference_no'] ?? null,
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'vendor_id' => $validated['vendor_id'] ?? null,
                'product_id' => $product->id,
                'warehouse_id' => $validated['warehouse_id'] ?? null,
                'qty_in' => $qtyIn,
                'qty_out' => $qtyOut,
                'unit' => $unit,
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => Auth::id(),
            ], $request->boolean('allow_negative', false));

            AuditLog::record(
                'inventory_movement',
                'inventory-ledger',
                "Recorded {$validated['transaction_type']} for {$product->name} (Qty In: {$qtyIn}, Qty Out: {$qtyOut}, Balance: {$ledger->balance_qty})"
            );

            return redirect()->route('inventory-ledger.index')
                ->with('success', "Inventory transaction recorded. New stock balance for {$product->name}: {$ledger->balance_qty} {$unit}.");
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()]);
        }
    }

    public function show(InventoryLedger $inventoryLedger)
    {
        $inventoryLedger->load(['product.category', 'vendor', 'purchaseOrder', 'createdBy']);

        return view('admin.inventory.show', compact('inventoryLedger'));
    }

    /**
     * PO Consumption & Fulfillment Tracking screen
     */
    public function poTracking(Request $request)
    {
        $query = Procurement::with(['vendor', 'items.product'])->latest('order_date');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('po_number', 'like', "%{$search}%");
        }

        $pos = $query->paginate(15)->withQueryString();

        // Calculate PO consumption metrics per PO item
        $poMetrics = [];
        foreach ($pos as $po) {
            foreach ($po->items as $item) {
                $ordered = (float) $item->quantity;
                $received = (float) $item->received_quantity;
                $pending = max(0, $ordered - $received);

                // Consumed qty tracked in inventory ledgers under type 'issue' against this PO & product
                $consumed = (float) InventoryLedger::where('purchase_order_id', $po->id)
                    ->where('product_id', $item->product_id)
                    ->where('transaction_type', 'issue')
                    ->sum('qty_out');

                $available = max(0, $received - $consumed);

                $poMetrics[$po->id][$item->id] = [
                    'ordered' => $ordered,
                    'received' => $received,
                    'pending' => $pending,
                    'consumed' => $consumed,
                    'available' => $available,
                ];
            }
        }

        $vendors = Vendor::orderBy('name')->get(['id', 'name']);

        return view('admin.inventory.po-tracking', compact('pos', 'poMetrics', 'vendors'));
    }

    public function exportExcel(Request $request)
    {
        $query = InventoryLedger::with(['product', 'vendor', 'purchaseOrder'])->latest('transaction_date');

        if ($request->filled('date_from')) $query->whereDate('transaction_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('transaction_date', '<=', $request->date_to);
        if ($request->filled('transaction_type')) $query->where('transaction_type', $request->transaction_type);
        if ($request->filled('product_id')) $query->where('product_id', $request->product_id);

        $records = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory_ledger_' . date('Ymd_His') . '.csv"',
        ];

        return new StreamedResponse(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Transaction Type', 'Reference No', 'PO No', 'Vendor', 'Item Code', 'Item Name', 'Qty In', 'Qty Out', 'Balance Qty', 'Unit', 'Remarks']);

            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->transaction_date->format('Y-m-d'),
                    strtoupper($r->transaction_type),
                    $r->reference_no,
                    $r->purchaseOrder?->po_number ?? '—',
                    $r->vendor?->name ?? '—',
                    $r->product?->sku ?? '—',
                    $r->product?->name ?? '—',
                    $r->qty_in,
                    $r->qty_out,
                    $r->balance_qty,
                    $r->unit,
                    $r->remarks,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function print(Request $request)
    {
        $query = InventoryLedger::with(['product', 'vendor', 'purchaseOrder'])->latest('transaction_date');

        if ($request->filled('date_from')) $query->whereDate('transaction_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('transaction_date', '<=', $request->date_to);
        if ($request->filled('transaction_type')) $query->where('transaction_type', $request->transaction_type);
        if ($request->filled('product_id')) $query->where('product_id', $request->product_id);

        $ledgers = $query->take(500)->get();

        return view('admin.inventory.print', compact('ledgers'));
    }
}

