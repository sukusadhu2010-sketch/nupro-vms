<?php

namespace App\Http\Controllers\Admin;

use App\Events\SalesOrderCreated;
use App\Exceptions\QuotationAlreadyConvertedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Services\SalesOrderService;
use Illuminate\Http\Request;

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

        return view('admin.sales-orders.create', compact('quotations'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $salesOrders = SalesOrder::with(['customer', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.sales-orders.index', compact('salesOrders'));
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
                'po_number' => $request->input('po_number'),
                'job_number' => $request->input('job_number'),
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
            'po_number' => 'nullable|string|max:255',
            'job_number' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string|max:2000',
            'expected_delivery_date' => 'nullable|date|after_or_equal:today',
        ]);

        $salesOrder->update($request->only([
            'status',
            'po_number',
            'job_number',
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

