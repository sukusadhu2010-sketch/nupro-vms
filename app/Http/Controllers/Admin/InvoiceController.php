<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\OrganizationSetting;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'salesOrder'])->latest('invoice_date');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('po_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        // Metrics for dashboard cards
        $metrics = [
            'total_invoiced' => Invoice::where('status', '!=', 'cancelled')->sum('grand_total'),
            'total_paid' => Invoice::where('status', '!=', 'cancelled')->sum('paid_amount'),
            'total_outstanding' => Invoice::where('status', '!=', 'cancelled')->get()->sum->outstanding,
            'count_pending' => Invoice::whereIn('status', ['draft', 'pending'])->count(),
        ];

        $customers = Customer::orderBy('name')->get(['id', 'name']);

        return view('admin.invoices.index', compact('invoices', 'metrics', 'customers'));
    }

    public function create(Request $request)
    {
        $invoiceNumber = Invoice::generateInvoiceNumber($request->query('type', 'tax'));
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $salesOrders = SalesOrder::with(['customer', 'items.product'])->latest()->get();

        $selectedSalesOrder = null;
        if ($request->filled('sales_order_id')) {
            $selectedSalesOrder = SalesOrder::with(['customer', 'items.product'])->find($request->sales_order_id);
        }

        return view('admin.invoices.create', compact('invoiceNumber', 'customers', 'products', 'salesOrders', 'selectedSalesOrder'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'type' => 'required|in:tax,proforma,commercial',
            'invoice_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'po_number' => 'nullable|string|max:100',
            'po_date' => 'nullable|date',
            'customer_gst' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
            'cess' => 'nullable|numeric|min:0',
            'tcs' => 'nullable|numeric|min:0',
            'shipping_charge' => 'nullable|numeric|min:0',
        ]);

        $invoice = DB::transaction(function () use ($request, $validated) {
            $invoice = Invoice::create([
                'invoice_number' => $validated['invoice_number'],
                'type' => $validated['type'],
                'invoice_date' => $validated['invoice_date'],
                'customer_id' => $validated['customer_id'],
                'sales_order_id' => $validated['sales_order_id'] ?? null,
                'po_number' => $validated['po_number'] ?? null,
                'po_date' => $validated['po_date'] ?? null,
                'customer_gst' => $validated['customer_gst'] ?? null,
                'billing_address' => $validated['billing_address'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'cess' => $request->input('cess', 0),
                'tcs' => $request->input('tcs', 0),
                'shipping_charge' => $request->input('shipping_charge', 0),
                'terms' => $validated['terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->input('items', []) as $itemData) {
                $qty = (float) $itemData['quantity'];
                $rate = (float) $itemData['rate'];
                $discPct = (float) ($itemData['discount_percent'] ?? 0);
                $taxPct = (float) ($itemData['tax_percent'] ?? 18);

                $gross = $qty * $rate;
                $discAmt = round($gross * ($discPct / 100), 2);
                $taxable = $gross - $discAmt;
                $taxAmt = round($taxable * ($taxPct / 100), 2);
                $amount = round($taxable + $taxAmt, 2);

                $invoice->items()->create([
                    'product_id' => $itemData['product_id'] ?? null,
                    'sales_order_item_id' => $itemData['sales_order_item_id'] ?? null,
                    'item_name' => $itemData['item_name'],
                    'description' => $itemData['description'] ?? null,
                    'hsn_code' => $itemData['hsn_code'] ?? null,
                    'quantity' => $qty,
                    'unit' => $itemData['unit'] ?? 'Nos',
                    'rate' => $rate,
                    'discount_percent' => $discPct,
                    'discount_amount' => $discAmt,
                    'tax_percent' => $taxPct,
                    'tax_amount' => $taxAmt,
                    'amount' => $amount,
                ]);
            }

            $invoice->recalcTotals();

            AuditLog::record('invoice_created', 'invoices', "Created {$invoice->type} invoice #{$invoice->invoice_number} for {$invoice->customer?->name}");

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} created successfully.");
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'salesOrder', 'items.product', 'allocations.payment', 'createdBy']);
        $org = OrganizationSetting::current();

        return view('admin.invoices.show', compact('invoice', 'org'));
    }

    public function edit(Invoice $invoice)
    {
        if (in_array($invoice->status, ['paid', 'partially_paid', 'cancelled'])) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', "Invoices in '{$invoice->status}' status cannot be edited.");
        }

        $invoice->load(['items', 'customer']);
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $salesOrders = SalesOrder::latest()->get();

        return view('admin.invoices.edit', compact('invoice', 'customers', 'products', 'salesOrders'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if (in_array($invoice->status, ['paid', 'partially_paid', 'cancelled'])) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', "Invoices in '{$invoice->status}' status cannot be edited.");
        }

        $validated = $request->validate([
            'type' => 'required|in:tax,proforma,commercial',
            'invoice_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'po_number' => 'nullable|string|max:100',
            'po_date' => 'nullable|date',
            'customer_gst' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
            'cess' => 'nullable|numeric|min:0',
            'tcs' => 'nullable|numeric|min:0',
            'shipping_charge' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $invoice, $validated) {
            $invoice->update([
                'type' => $validated['type'],
                'invoice_date' => $validated['invoice_date'],
                'customer_id' => $validated['customer_id'],
                'sales_order_id' => $validated['sales_order_id'] ?? null,
                'po_number' => $validated['po_number'] ?? null,
                'po_date' => $validated['po_date'] ?? null,
                'customer_gst' => $validated['customer_gst'] ?? null,
                'billing_address' => $validated['billing_address'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'cess' => $request->input('cess', 0),
                'tcs' => $request->input('tcs', 0),
                'shipping_charge' => $request->input('shipping_charge', 0),
                'terms' => $validated['terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Replace items
            $invoice->items()->delete();

            foreach ($request->input('items', []) as $itemData) {
                $qty = (float) $itemData['quantity'];
                $rate = (float) $itemData['rate'];
                $discPct = (float) ($itemData['discount_percent'] ?? 0);
                $taxPct = (float) ($itemData['tax_percent'] ?? 18);

                $gross = $qty * $rate;
                $discAmt = round($gross * ($discPct / 100), 2);
                $taxable = $gross - $discAmt;
                $taxAmt = round($taxable * ($taxPct / 100), 2);
                $amount = round($taxable + $taxAmt, 2);

                $invoice->items()->create([
                    'product_id' => $itemData['product_id'] ?? null,
                    'sales_order_item_id' => $itemData['sales_order_item_id'] ?? null,
                    'item_name' => $itemData['item_name'],
                    'description' => $itemData['description'] ?? null,
                    'hsn_code' => $itemData['hsn_code'] ?? null,
                    'quantity' => $qty,
                    'unit' => $itemData['unit'] ?? 'Nos',
                    'rate' => $rate,
                    'discount_percent' => $discPct,
                    'discount_amount' => $discAmt,
                    'tax_percent' => $taxPct,
                    'tax_amount' => $taxAmt,
                    'amount' => $amount,
                ]);
            }

            $invoice->recalcTotals();

            AuditLog::record('invoice_updated', 'invoices', "Updated invoice #{$invoice->invoice_number}");
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} updated successfully.");
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->paid_amount > 0 || $invoice->status === 'paid') {
            return response()->json(['status' => false, 'message' => 'Cannot delete an invoice that has payments recorded.'], 422);
        }

        $num = $invoice->invoice_number;
        $invoice->items()->delete();
        $invoice->delete();

        AuditLog::record('invoice_deleted', 'invoices', "Deleted invoice #{$num}");

        return response()->json(['status' => true, 'message' => "Invoice {$num} deleted successfully."]);
    }

    public function approve(Invoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'Cannot approve a cancelled invoice.');
        }

        $invoice->update(['status' => 'approved']);
        AuditLog::record('invoice_approved', 'invoices', "Approved invoice #{$invoice->invoice_number}");

        return back()->with('success', "Invoice {$invoice->invoice_number} approved successfully.");
    }

    public function cancel(Request $request, Invoice $invoice)
    {
        if ($invoice->paid_amount > 0) {
            return back()->with('error', 'Cannot cancel an invoice with allocated payments. Reverse payments first.');
        }

        $reason = $request->input('reason', 'Cancelled by user');
        $invoice->update([
            'status' => 'cancelled',
            'notes' => ($invoice->notes ? $invoice->notes . "\n" : '') . "Cancellation Reason: {$reason}",
        ]);

        AuditLog::record('invoice_cancelled', 'invoices', "Cancelled invoice #{$invoice->invoice_number}. Reason: {$reason}");

        return back()->with('success', "Invoice {$invoice->invoice_number} cancelled.");
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'salesOrder', 'items.product', 'createdBy']);
        $org = OrganizationSetting::current();

        return view('admin.invoices.print', compact('invoice', 'org'));
    }

    /**
     * AJAX endpoint: fetch SO details to prefill invoice create
     */
    public function fetchSalesOrder(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.product']);

        return response()->json([
            'customer_id' => $salesOrder->customer_id,
            'customer_name' => $salesOrder->customer?->name,
            'customer_gst' => $salesOrder->customer?->gstin ?? $salesOrder->customer?->gst_no,
            'billing_address' => $salesOrder->customer?->address ?? $salesOrder->shipping_address,
            'shipping_address' => $salesOrder->shipping_address ?? $salesOrder->customer?->address,
            'po_number' => $salesOrder->customer_po_number,
            'po_date' => $salesOrder->customer_po_date?->format('Y-m-d'),
            'items' => $salesOrder->items->map(fn ($item) => [
                'sales_order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'item_name' => $item->product?->name ?? 'Item',
                'description' => $item->product?->description ?? '',
                'hsn_code' => $item->product?->hsn_code ?? '',
                'quantity' => $item->quantity,
                'unit' => $item->product?->unit?->short_name ?? 'Nos',
                'rate' => $item->unit_price,
                'tax_percent' => 18,
            ]),
        ]);
    }
}

