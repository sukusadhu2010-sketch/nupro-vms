<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Procurement;
use App\Models\ProcurementItem;
use App\Models\ProcurementReceipt;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Vendor;
use App\Services\ProcurementService;
use App\Services\InventoryService;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcurementController extends Controller
{
    public function __construct(
        private ProcurementService $procurementService,
        private InventoryService $inventoryService,
        private LedgerService $ledgerService
    ) {
    }

    /**
     * Display a listing of procurements.
     */
public function index()
    {
        $procurements = Procurement::with(['vendor', 'salesOrder.customer', 'salesOrders.customer', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.procurements.index', compact('procurements'));
    }

/**
     * Show the form for creating a new procurement manually.
     */
    public function create()
    {
        $vendors = Vendor::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('status', 'active')->with('vendor')->orderBy('name')->get();
        $salesOrders = SalesOrder::whereIn('status', ['confirmed', 'processing'])
            ->with(['items.product', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Prepare sales order items mapping for JS auto-population (combining all selected SOs)
        $salesOrderItemsMap = $salesOrders->mapWithKeys(function ($so) {
            return [
                $so->id => $so->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name ?? 'N/A',
                        'product_sku' => $item->product->sku ?? '',
                        'product_price' => $item->product->price ?? 0,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'sales_order_id' => $item->sales_order_id,
                    ];
                })->values()->toArray(),
            ];
        })->toArray();

        return view('admin.procurements.create', compact('vendors', 'products', 'salesOrders', 'salesOrderItemsMap'));
    }

/**
     * Store a newly created procurement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_ids' => 'nullable|array',
            'sales_order_ids.*' => 'exists:sales_orders,id',
            'vendor_id' => 'required|exists:vendors,id',
            'po_number' => 'required|string|max:255|unique:procurements,po_number',
            'order_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'shipping_address' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:5000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:1000',
            'attachments.*' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $procurement = DB::transaction(function () use ($request, $validated) {
            $totalAmount = collect($validated['items'])->sum(
                fn ($item) => $item['quantity'] * $item['unit_price']
            );

            // Handle attachments
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $attachments[] = $file->store('procurements', 'public');
                }
            }

            // Get first sales order for backward compatibility
            $salesOrderIds = $validated['sales_order_ids'] ?? [];
            $firstSalesOrderId = ! empty($salesOrderIds) ? $salesOrderIds[0] : null;

            $procurement = Procurement::create([
                'sales_order_id' => $firstSalesOrderId,
                'vendor_id' => $validated['vendor_id'],
                'po_number' => $validated['po_number'],
                'status' => 'draft',
                'total_amount' => $totalAmount,
                'order_date' => $validated['order_date'] ?? now()->toDateString(),
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'attachments' => $attachments,
            ]);

            // Attach multiple sales orders
            if (! empty($salesOrderIds)) {
                $attachData = [];
                foreach ($salesOrderIds as $salesOrderId) {
                    $attachData[$salesOrderId] = ['quantity' => 0];
                }
                $procurement->salesOrders()->attach($attachData);
            }

            foreach ($validated['items'] as $item) {
                ProcurementItem::create([
                    'procurement_id' => $procurement->id,
                    'sales_order_item_id' => $item['sales_order_item_id'] ?? null,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return $procurement;
        });

        return redirect()
            ->route('procurements.show', $procurement)
            ->with('success', 'Procurement ' . $procurement->po_number . ' created successfully.');
    }

    /**
     * Generate vendor-wise procurements from a sales order.
     */
    public function generate(SalesOrder $salesOrder)
    {
        try {
            $procurements = $this->procurementService->generateFromSalesOrder($salesOrder);

            if (empty($procurements)) {
                return redirect()
                    ->back()
                    ->with('warning', 'No procurements could be generated. Ensure sales order items have associated vendors.');
            }

            $count = count($procurements);
            $poNumbers = collect($procurements)->pluck('po_number')->implode(', ');

            return redirect()
                ->route('procurements.index')
                ->with('success', "{$count} procurement(s) generated successfully: {$poNumbers}");
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to generate procurements: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified procurement.
     */
    public function show(Procurement $procurement)
    {
        $procurement->load(['vendor', 'salesOrder.customer', 'salesOrders.customer', 'items.product', 'items.receipts.user', 'payments']);

        return view('admin.procurements.show', compact('procurement'));
    }

    /**
     * Show payment entry form for procurement.
     */
    public function paymentEntry(Procurement $procurement)
    {
        if ($procurement->status === 'cancelled') {
            return redirect()->back()->with('error', 'Cannot record payment for cancelled procurement.');
        }

        if ($procurement->outstanding_amount <= 0) {
            return redirect()->back()->with('error', 'Procurement is already fully paid.');
        }

        $procurement->load(['vendor', 'payments']);

        return view('admin.procurements.payment-entry', compact('procurement'));
    }

    /**
     * Store vendor payment.
     */
    public function storePayment(Request $request, Procurement $procurement)
    {
        $rules = [
            'amount' => 'required|numeric|min:0.01|max:' . $procurement->outstanding_amount,
            'payment_mode' => 'required|in:cash,fund_transfer,cheque,upi',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ];

        if ($request->payment_mode === 'upi') {
            $rules['upi_id'] = 'required|string|max:100';
        }
        if ($request->payment_mode === 'cheque') {
            $rules['cheque_number'] = 'required|string|max:50';
            $rules['cheque_date'] = 'required|date';
            $rules['bank_name'] = 'required|string|max:100';
        }
        if ($request->payment_mode === 'fund_transfer') {
            $rules['beneficiary_details'] = 'required|string|max:500';
            $rules['transaction_id'] = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($procurement, $validated) {
            $paymentData = array_merge($validated, [
                'payable_id' => $procurement->id,
                'payable_type' => Procurement::class,
                'transaction_type' => 'expense',
                'contact_id' => $procurement->vendor_id,
                'contact_type' => 'vendor',
                'ledger_account_id' => app(LedgerService::class)->getLedgerAccount($validated['payment_mode'])->id,
                'created_by' => Auth::id(),
            ]);

            $payment = $procurement->payments()->create($paymentData);

            app(LedgerService::class)->recordPayment($payment, false); // expense = negative

            // Update procurement paid_amount aggregate
            $procurement->update([
                'paid_amount' => $procurement->payments()->where('transaction_type', 'expense')->sum('amount')
            ]);
        });

        return redirect()->route('procurements.show', $procurement)
            ->with('success', 'Vendor payment ₹' . number_format($validated['amount'], 2) . ' recorded. Outstanding: ₹' . number_format($procurement->fresh()->outstanding_amount, 2));
    }

    /**
     * Show the form for editing the specified procurement.
     */
public function edit(Procurement $procurement)
    {
        $procurement->load(['items.product', 'items.receipts.user', 'salesOrders']);
        $vendors = Vendor::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('status', 'active')->orderBy('name')->get();
        
        // Get all sales orders for multi-select (including already linked ones)
        $salesOrders = SalesOrder::whereIn('status', ['confirmed', 'processing'])
            ->with(['items.product', 'customer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.procurements.edit', compact('procurement', 'vendors', 'products', 'salesOrders'));
    }

    /**
     * Update the specified procurement.
     */
    public function update(Request $request, Procurement $procurement)
    {
        // When procurement is fully received, only status change to 'sent' is permitted
        if ($procurement->status === 'received') {
            $validated = $request->validate([
                'status' => 'required|in:sent',
            ]);

            $procurement->update(['status' => $validated['status']]);

            return redirect()
                ->route('procurements.show', $procurement)
                ->with('success', 'Procurement status updated to Sent.');
        }

$validated = $request->validate([
            'vendor_id' => 'sometimes|exists:vendors,id',
            'sales_order_ids' => 'sometimes|array',
            'sales_order_ids.*' => 'exists:sales_orders,id',
            'status' => 'required|in:draft,sent,partially_received,received,cancelled',
            'expected_delivery_date' => 'nullable|date',
            'actual_delivery_date' => 'nullable|date',
            'shipping_address' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:5000',
            'items' => 'sometimes|array',
            'items.*.id' => 'nullable|exists:procurement_items,id',
            'items.*.product_id' => 'required_with:items.*.quantity|exists:products,id',
            'items.*.quantity' => 'required_with:items.*.product_id|integer|min:1',
            'items.*.unit_price' => 'required_with:items.*.product_id|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:1000',
            'items.*.receive_now' => 'nullable|integer|min:0',
            'delete_items' => 'nullable|array',
            'delete_items.*' => 'exists:procurement_items,id',
            'attachments.*' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        // Handle attachments
        $attachments = $procurement->attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('procurements', 'public');
            }
        }

$procurement->update(array_merge(
            $request->only([
                'status',
                'expected_delivery_date',
                'actual_delivery_date',
                'shipping_address',
                'notes',
            ]),
            ['attachments' => $attachments]
        ));

// Handle vendor update (if provided and allowed)
        if ($request->has('vendor_id') && $procurement->status !== 'received') {
            $procurement->update(['vendor_id' => $validated['vendor_id']]);
        }

        // Handle multiple sales orders update
        if ($request->has('sales_order_ids')) {
            $salesOrderIds = $validated['sales_order_ids'] ?? [];
            
            // Sync with pivot table (new format)
            $attachData = [];
            foreach ($salesOrderIds as $salesOrderId) {
                $attachData[$salesOrderId] = ['quantity' => 0];
            }
            $procurement->salesOrders()->sync($attachData);
            
// Update legacy sales_order_id to first one for backward compatibility
            if (! empty($salesOrderIds)) {
                $procurement->update(['sales_order_id' => $salesOrderIds[0]]);
            }
        }

        // Handle delete items
        $deleteItemIds = $validated['delete_items'] ?? [];
        if (! empty($deleteItemIds)) {
            ProcurementItem::whereIn('id', $deleteItemIds)->where('procurement_id', $procurement->id)->delete();
        }

        if (! empty($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                // Check if it's a new item (no id) or existing item (has id)
                if (empty($itemData['id'])) {
                    // New item - create it
                    if (! empty($itemData['product_id']) && ! empty($itemData['quantity'])) {
                        ProcurementItem::create([
                            'procurement_id' => $procurement->id,
                            'product_id' => $itemData['product_id'],
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                            'subtotal' => $itemData['quantity'] * $itemData['unit_price'],
                            'notes' => $itemData['notes'] ?? null,
                        ]);
                    }
                } else {
                    // Existing item - process receipt only
                    $item = ProcurementItem::find($itemData['id']);
                    $receiveNow = (int) ($itemData['receive_now'] ?? 0);

                    if (! $item || $item->procurement_id !== $procurement->id || $receiveNow <= 0) {
                        continue;
                    }

                    $pending = $item->quantity - $item->received_quantity;

                    if ($receiveNow > $pending) {
                        return redirect()
                            ->back()
                            ->with('error', "Cannot receive {$receiveNow} units for {$item->product->name}. Only {$pending} pending.");
                    }

                    // Create receipt record
                    ProcurementReceipt::create([
                        'procurement_id' => $procurement->id,
                        'procurement_item_id' => $item->id,
                        'quantity_received' => $receiveNow,
                        'received_at' => now(),
                        'user_id' => Auth::id(),
                        'notes' => $itemData['receipt_notes'] ?? null,
                    ]);

                    // Update aggregated received quantity
                    $item->update([
                        'received_quantity' => $item->received_quantity + $receiveNow,
                    ]);
                }
            }

// Auto-update procurement status based on received quantities
            $procurement->load('items');
            $allReceived = $procurement->items->every(fn ($i) => $i->received_quantity >= $i->quantity);
            $someReceived = $procurement->items->some(fn ($i) => $i->received_quantity > 0);

            if ($allReceived && $procurement->status !== 'cancelled') {
                $procurement->update(['status' => 'received', 'actual_delivery_date' => now()->toDateString()]);
                
                // Update inventory when fully received - add stock
                try {
                    $this->inventoryService->addStockFromProcurement($procurement);
                } catch (\Throwable $e) {
                    // Log error but don't fail the procurement update
                    \Log::error('Inventory update failed: ' . $e->getMessage());
                }
            } elseif ($someReceived && $procurement->status !== 'cancelled') {
                $procurement->update(['status' => 'partially_received']);
                
                // Update inventory for partially received items
                try {
                    $this->inventoryService->addStockFromProcurement($procurement);
                } catch (\Throwable $e) {
                    \Log::error('Inventory update failed: ' . $e->getMessage());
                }
            }
        }

        return redirect()
            ->route('procurements.show', $procurement)
            ->with('success', 'Procurement updated successfully. Inventory has been updated.');
    }

    /**
     * Quick status update from listing page.
     */
    public function updateStatus(Request $request, Procurement $procurement)
    {
        // When procurement is fully received, only status change to 'sent' is permitted
        if ($procurement->status === 'received') {
            $validated = $request->validate([
                'status' => 'required|in:sent',
            ]);

            $procurement->update(['status' => $validated['status']]);

            return redirect()
                ->back()
                ->with('success', 'Procurement status updated to Sent.');
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,sent,partially_received,received,cancelled',
        ]);

        $procurement->update(['status' => $validated['status']]);

        if ($validated['status'] === 'received') {
            $procurement->update(['actual_delivery_date' => now()->toDateString()]);
        }

        return redirect()
            ->back()
            ->with('success', 'Status updated to ' . $procurement->status_label);
    }

    /**
     * Remove the specified procurement.
     */
    public function destroy(Procurement $procurement)
    {
        $procurement->delete();

        return redirect()
            ->route('procurements.index')
            ->with('success', 'Procurement deleted.');
    }
}

