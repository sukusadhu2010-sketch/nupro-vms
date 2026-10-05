<?php

namespace App\Http\Controllers\Admin;

use App\Events\SalesOrderCreated;
use App\Exceptions\QuotationAlreadyConvertedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Services\SalesOrderExportService;
use App\Services\SalesOrderService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SalesOrderController extends Controller
{
    public function __construct(
        private SalesOrderService $salesOrderService
    ) {
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $quotations = \App\Models\Quotation::with('enquiry.customer')
            ->where('status', 'accepted')
            ->orWhere('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();

        $nextJobNumber = app(\App\Services\JobNumberService::class)->generate();

        return view('admin.sales-orders.create', compact('quotations', 'nextJobNumber'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $this->extractFilters($request);

        $salesOrders = $this->applyFilters(SalesOrder::query(), $filters)
            ->with(['customer', 'items.product.productCategory'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $totalRecords = $salesOrders->total();

        $customers = \App\Models\Customer::orderBy('name')->get(['id', 'name']);
        $products  = \App\Models\Product::orderBy('name')->get(['id', 'name']);

        return view('admin.sales-orders.index', compact(
            'salesOrders', 'totalRecords', 'customers', 'products', 'filters'
        ));
    }

    /**
     * Pull & normalise filter inputs from the request.
     */
    private function extractFilters(Request $request): array
    {
        return array_filter([
            'date_from'   => $request->input('date_from'),
            'date_to'     => $request->input('date_to'),
            'date_field'  => $request->input('date_field', 'customer_po_date') === 'created_at' ? 'created_at' : 'customer_po_date',
            'status'      => $request->input('status') === 'all' ? null : $request->input('status'),
            'customer_id' => $request->filled('customer_id') ? (int) $request->input('customer_id') : null,
            'product_id'  => $request->filled('product_id') ? (int) $request->input('product_id') : null,
            'job_number'  => $request->input('job_number'),
            'po_no'       => $request->input('po_no'),
            'remarks'     => $request->input('remarks'),
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Apply all filters to a SalesOrder query (server-side, composable).
     */
    private function applyFilters($query, array $filters)
    {
        if (! empty($filters['date_from'])) {
            $query->whereDate($filters['date_field'] ?? 'customer_po_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate($filters['date_field'] ?? 'customer_po_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (! empty($filters['product_id'])) {
            $query->whereHas('items', fn ($q) => $q->where('product_id', $filters['product_id']));
        }

        if (! empty($filters['job_number'])) {
            $query->where('job_number', 'like', '%' . $filters['job_number'] . '%');
        }

        if (! empty($filters['po_no'])) {
            $query->where('customer_po_number', 'like', '%' . $filters['po_no'] . '%');
        }

        if (! empty($filters['remarks'])) {
            $query->whereHas('items.product', fn ($q) => $q->where('products.description', 'like', '%' . $filters['remarks'] . '%'));
        }

        return $query;
    }

    /**
     * Export filtered records to Excel (.xlsx).
     */
    public function exportExcel(Request $request, SalesOrderExportService $exportService)
    {
        $filters = $this->extractFilters($request);

        $salesOrders = $this->applyFilters(SalesOrder::query(), $filters)
            ->with(['customer', 'items.product.productCategory'])
            ->orderBy('created_at', 'desc')
            ->get();

        $spreadsheet = $exportService->exportExcel($salesOrders, $filters, $salesOrders->count());

        return $exportService->downloadExcel($spreadsheet);
    }

    /**
     * Export filtered records to PDF (landscape, repeating header, page numbers).
     */
    public function exportPdf(Request $request, SalesOrderExportService $exportService)
    {
        $filters = $this->extractFilters($request);

        $salesOrders = $this->applyFilters(SalesOrder::query(), $filters)
            ->with(['customer', 'items.product.productCategory'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $exportService->exportPdf($salesOrders, $filters, $salesOrders->count());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSalesOrderRequest $request)
    {
        $quotation = Quotation::with(['items', 'enquiry.customer'])
            ->findOrFail($request->quotation_id);

        try {
            $salesOrder = $this->salesOrderService->convertToOrder($quotation, [
                'job_number' => $request->input('job_number'), // optional; auto-generated when omitted
                'customer_po_number' => $request->input('customer_po_number'),
                'customer_po_date' => $request->input('customer_po_date'),
                'mtc' => $request->boolean('mtc'),
                'pdi' => $request->boolean('pdi'),
                'delivery_target_date' => $request->input('delivery_target_date'),
                'payment_mode' => $request->input('payment_mode'),
                'credit_days' => $request->input('credit_days'),
            ]);

            return redirect()
                ->route('sales-orders.show', $salesOrder)
                ->with('success', 'Sales order ' . $salesOrder->order_number . ' created successfully.');
        } catch (QuotationAlreadyConvertedException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create sales order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer.user', 'items.product.vendor', 'quotation']);

        return view('admin.sales-orders.show', compact('salesOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesOrder $salesOrder)
    {
        return view('admin.sales-orders.edit', compact('salesOrder'));
    }

/**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesOrder $salesOrder)
    {
        $isLocked = in_array($salesOrder->status, ['confirmed', 'processing', 'shipped', 'delivered']);

        if ($salesOrder->status === 'delivered') {
            return redirect()
                ->route('sales-orders.show', $salesOrder)
                ->with('error', 'This sales order is locked because it has been delivered. No further modifications are permitted.');
        }

        // If order is locked (confirmed or higher), only allow status update
        if ($isLocked) {
            $request->validate([
                'status' => 'required|in:confirmed,processing,shipped,delivered,cancelled',
            ]);

            // Validate status progression
            $statusOrder = ['draft' => 0, 'confirmed' => 1, 'processing' => 2, 'shipped' => 3, 'delivered' => 4];
            $currentStatusLevel = $statusOrder[$salesOrder->status] ?? 0;
            $newStatusLevel = $statusOrder[$request->status] ?? 0;

            // Only allow moving forward or cancelling
            if ($newStatusLevel < $currentStatusLevel && $request->status !== 'cancelled') {
                return back()
                    ->with('error', 'Cannot revert status. You can only move forward in status or cancel.');
            }

            $salesOrder->update(['status' => $request->status]);

            return redirect()
                ->route('sales-orders.show', $salesOrder)
                ->with('success', 'Sales order status updated to ' . ucfirst($request->status) . '.');
        }

        // Not locked yet - allow full editing
        $request->validate([
            'status' => 'required|in:draft,confirmed,processing,shipped,delivered,cancelled',
            'job_number' => 'nullable|string|max:255',
            'customer_po_number' => 'nullable|string|max:255',
            'customer_po_date' => 'nullable|date',
            'mtc' => 'nullable|boolean',
            'pdi' => 'nullable|boolean',
            'sd_pbg' => 'nullable|boolean',
            'delivery_target_date' => 'nullable|date|after_or_equal:today',
            'payment_mode' => 'nullable|in:' . implode(',', SalesOrder::PAYMENT_MODES),
            'credit_days' => 'nullable|integer|min:1|max:365|required_if:payment_mode,lc,credit',
            'shipping_address' => 'nullable|string|max:2000',
            'expected_delivery_date' => 'nullable|date|after_or_equal:today',
        ]);

        $salesOrder->update($request->only([
            'status',
            'job_number',
            'customer_po_number',
            'customer_po_date',
            'mtc',
            'pdi',
            'sd_pbg',
            'delivery_target_date',
            'payment_mode',
            'credit_days',
            'shipping_address',
            'expected_delivery_date',
        ]));

        return redirect()
            ->route('sales-orders.show', $salesOrder)
            ->with('success', 'Sales order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesOrder $salesOrder)
    {
        $salesOrder->delete();

        return redirect()
            ->route('sales-orders.index')
            ->with('success', 'Sales order deleted.');
    }
}

