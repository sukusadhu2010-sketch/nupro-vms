<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SalesOrder;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'salesOrder']);

        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('invoice_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('customer', function ($cq) use ($request) {
                        $cq->where('company', 'like', '%' . $request->search . '%')
                            ->orWhere('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);
        $customers = Customer::where('status', 'active')->orderBy('company')->get();

        return view('admin.invoices.index', compact('invoices', 'customers'));
    }

    /**
     * Show the form for creating a new invoice from sales order.
     */
    public function create(Request $request)
    {
        $salesOrderId = $request->get('sales_order_id');
        
        $salesOrders = SalesOrder::whereIn('status', ['confirmed', 'processing', 'delivered'])
            ->whereDoesntHave('invoices', function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            })
            ->with(['customer', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        $selectedSalesOrder = null;
        if ($salesOrderId) {
            $selectedSalesOrder = SalesOrder::with(['customer', 'items.product'])->find($salesOrderId);
        }

        return view('admin.invoices.create', compact('salesOrders', 'selectedSalesOrder'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string|max:5000',
            'items' => 'required|array|min:1',
            'items.*.sales_order_item_id' => 'required|exists:sales_order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice = DB::transaction(function () use ($validated, $request) {
            $salesOrder = SalesOrder::findOrFail($validated['sales_order_id']);

            // Calculate totals
            $subtotal = 0;
            $items = [];
            foreach ($validated['items'] as $itemData) {
                $itemSubtotal = $itemData['quantity'] * $itemData['unit_price'];
                $subtotal += $itemSubtotal;
                
                $salesOrderItem = \App\Models\SalesOrderItem::findOrFail($itemData['sales_order_item_id']);
                
                $items[] = [
                    'sales_order_item_id' => $itemData['sales_order_item_id'],
                    'product_id' => $salesOrderItem->product_id,
                    'description' => $salesOrderItem->product->name ?? 'Product',
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemSubtotal,
                ];
            }

            $taxAmount = $request->has('tax_rate') ? ($subtotal * $request->tax_rate / 100) : 0;
            $totalAmount = $subtotal + $taxAmount;

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'sales_order_id' => $validated['sales_order_id'],
                'customer_id' => $salesOrder->customer_id,
                'status' => 'draft',
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Create invoice items
            foreach ($items as $item) {
                $item['invoice_id'] = $invoice->id;
                InvoiceItem::create($item);
            }

            // Log audit
            $invoice->logAudit('created', null, [
                'invoice_number' => $invoice->invoice_number,
                'total_amount' => $totalAmount,
            ], 'Invoice created from sales order #' . $salesOrder->order_number);

            return $invoice;
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' created successfully.');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
$invoice->load(['customer', 'salesOrder', 'items.product', 'auditLogs.user', 'creator', 'canceller', 'payments']);

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice)
    {
        if ($invoice->status === 'cancelled' || $invoice->status === 'paid') {
            return redirect()
                ->back()
                ->with('error', 'Cannot edit a cancelled or paid invoice.');
        }

        $invoice->load(['customer', 'salesOrder', 'items.product']);

        return view('admin.invoices.edit', compact('invoice'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'cancelled' || $invoice->status === 'paid') {
            return redirect()
                ->back()
                ->with('error', 'Cannot update a cancelled or paid invoice.');
        }

        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string|max:5000',
        ]);

        $oldValues = $invoice->toArray();

        $invoice->update(array_merge($validated, [
            'updated_by' => Auth::id(),
        ]));

        $invoice->logAudit('updated', $oldValues, $invoice->fresh()->toArray(), 'Invoice details updated');

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

/**
     * Show the cancel form.
     */
    public function cancelForm(Invoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            return redirect()
                ->back()
                ->with('error', 'Invoice is already cancelled.');
        }

        if ($invoice->status === 'paid') {
            return redirect()
                ->back()
                ->with('error', 'Cannot cancel a paid invoice. Please process a refund first.');
        }

        return view('admin.invoices.cancel', compact('invoice'));
    }

    /**
     * Cancel the specified invoice.
     */
    public function cancel(Request $request, Invoice $invoice)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:1000',
        ]);

        if ($invoice->status === 'cancelled') {
            return redirect()
                ->back()
                ->with('error', 'Invoice is already cancelled.');
        }

        if ($invoice->status === 'paid') {
            return redirect()
                ->back()
                ->with('error', 'Cannot cancel a paid invoice. Please process a refund first.');
        }

        $invoice->cancel($request->cancellation_reason);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice cancelled successfully.');
    }

    /**
     * Mark invoice as sent.
     */
    public function sent(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()
                ->back()
                ->with('error', 'Only draft invoices can be marked as sent.');
        }

        $oldValues = $invoice->toArray();
        
        $invoice->update(['status' => 'sent']);
        
        $invoice->logAudit('sent', $oldValues, $invoice->fresh()->toArray(), 'Invoice marked as sent');

        return redirect()
            ->back()
            ->with('success', 'Invoice marked as sent.');
    }

    /**
     * Record payment for invoice.
     */
    public function payment(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            return redirect()
                ->back()
                ->with('error', 'Cannot record payment for a cancelled invoice.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->total_amount,
            'notes' => 'nullable|string|max:500',
        ]);

        $invoice->recordPayment($request->amount, $request->notes);

        return redirect()
            ->back()
            ->with('success', 'Payment recorded successfully.');
    }

/**
     * Generate invoice from sales order.
     */
    public function generateFromSalesOrder(SalesOrder $salesOrder)
    {
        // Check if sales order already has active invoices
        $existingInvoices = Invoice::where('sales_order_id', $salesOrder->id)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        if ($existingInvoices > 0) {
            return redirect()
                ->back()
                ->with('error', 'Sales order already has an active invoice.');
        }

        // Load sales order items
        $salesOrder->load(['customer', 'items.product']);

        // Create invoice with sales order items
        $invoice = DB::transaction(function () use ($salesOrder) {
            $subtotal = $salesOrder->items->sum('subtotal');
            $totalAmount = $subtotal; // Can add tax calculation here if needed

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id,
                'status' => 'draft',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Create invoice items from sales order items
            foreach ($salesOrder->items as $salesOrderItem) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'sales_order_item_id' => $salesOrderItem->id,
                    'product_id' => $salesOrderItem->product_id,
                    'description' => $salesOrderItem->product->name ?? 'Product',
                    'quantity' => $salesOrderItem->quantity,
                    'unit_price' => $salesOrderItem->unit_price,
                    'subtotal' => $salesOrderItem->subtotal,
                ]);
            }

            $invoice->logAudit('created', null, [
                'invoice_number' => $invoice->invoice_number,
                'total_amount' => $totalAmount,
            ], 'Invoice generated from sales order #' . $salesOrder->order_number);

            return $invoice;
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' generated successfully from sales order.');
    }

    /**
     * Print invoice.
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'salesOrder', 'items.product']);

        return view('admin.invoices.print', compact('invoice'));
    }

    /**
     * Show payment entry form for the invoice.
     */
    public function paymentEntry(Invoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            return redirect()->back()->with('error', 'Cannot record payment for cancelled invoice.');
        }

        if ($invoice->is_paid) {
            return redirect()->back()->with('error', 'Invoice is already fully paid.');
        }

        $invoice->load(['customer', 'payments']);

        return view('admin.invoices.payment-entry', compact('invoice'));
    }

    /**
     * Store new payment record.
     */
    public function storePayment(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            return redirect()->back()->with('error', 'Cannot record payment for cancelled invoice.');
        }

        $rules = [
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->outstanding_amount,
            'payment_mode' => 'required|in:cash,fund_transfer,cheque,upi',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ];

        // Mode-specific rules
        if ($request->payment_mode === 'upi') {
            $rules['upi_id'] = 'required|string|max:100';
        }
        if ($request->payment_mode === 'cheque') {
            $rules['cheque_number'] = 'required|string|max:50';
            $rules['bank_details'] = 'required|string|max:500';
        }
        if ($request->payment_mode === 'fund_transfer') {
            $rules['beneficiary_details'] = 'required|string|max:500';
            $rules['transaction_id'] = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($invoice, $validated) {
            $paymentData = $validated;
            $paymentData['invoice_id'] = $invoice->id;

            $invoice->recordPayment($paymentData);
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Payment of ₹' . number_format($validated['amount'], 2) . ' recorded successfully. Outstanding now: ₹' . number_format($invoice->fresh()->outstanding_amount, 2));
    }

}
