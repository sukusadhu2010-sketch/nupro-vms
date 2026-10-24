<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Procurement;
use App\Models\Vendor;
use Illuminate\Http\Request;

class PaymentReportController extends Controller
{
    /**
     * Report 1: Receipts Register
     */
    public function receiptsRegister(Request $request)
    {
        $query = Payment::with(['customer', 'allocations.invoice'])
            ->where(fn ($q) => $q->where('direction', Payment::DIRECTION_RECEIPT)->orWhere('direction', 'received'))
            ->latest('payment_date');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $receipts = $query->paginate(25)->withQueryString();
        $totalAmount = (clone $query)->sum('amount');
        $customers = Customer::orderBy('name')->get(['id', 'name']);

        return view('admin.reports.payments.receipts-register', compact('receipts', 'totalAmount', 'customers'));
    }

    /**
     * Report 2: Vendor Payment Register
     */
    public function vendorPaymentRegister(Request $request)
    {
        $query = Payment::with(['vendor', 'purchaseOrder'])
            ->where(fn ($q) => $q->where('direction', Payment::DIRECTION_PAYMENT)->orWhere('direction', 'paid'))
            ->latest('payment_date');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', $request->payment_mode);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->paginate(25)->withQueryString();
        $totalAmount = (clone $query)->sum('amount');
        $vendors = Vendor::orderBy('name')->get(['id', 'name']);

        return view('admin.reports.payments.vendor-payment-register', compact('payments', 'totalAmount', 'vendors'));
    }

    /**
     * Report 3: Aging Report (Receivables & Payables combined / detailed)
     */
    public function agingReport(Request $request)
    {
        $invoices = Invoice::with('customer')
            ->where('status', '!=', 'cancelled')
            ->whereRaw('grand_total > paid_amount')
            ->get();

        $agingSummary = [
            '0_30' => 0,
            '31_60' => 0,
            '61_90' => 0,
            '90_plus' => 0,
            'total' => 0,
        ];

        $customerAging = [];

        foreach ($invoices as $inv) {
            $days = now()->diffInDays($inv->invoice_date);
            $bal = (float) $inv->outstanding;
            $agingSummary['total'] += $bal;

            $custId = $inv->customer_id ?? 0;
            $custName = $inv->customer?->name ?? 'Unassigned';

            if (!isset($customerAging[$custId])) {
                $customerAging[$custId] = [
                    'customer' => $custName,
                    '0_30' => 0,
                    '31_60' => 0,
                    '61_90' => 0,
                    '90_plus' => 0,
                    'total' => 0,
                ];
            }

            if ($days <= 30) {
                $agingSummary['0_30'] += $bal;
                $customerAging[$custId]['0_30'] += $bal;
            } elseif ($days <= 60) {
                $agingSummary['31_60'] += $bal;
                $customerAging[$custId]['31_60'] += $bal;
            } elseif ($days <= 90) {
                $agingSummary['61_90'] += $bal;
                $customerAging[$custId]['61_90'] += $bal;
            } else {
                $agingSummary['90_plus'] += $bal;
                $customerAging[$custId]['90_plus'] += $bal;
            }
            $customerAging[$custId]['total'] += $bal;
        }

        return view('admin.reports.payments.aging', compact('agingSummary', 'customerAging'));
    }

    /**
     * Report 4: Receivable & Payable Summary
     */
    public function summaryReport(Request $request)
    {
        $totalReceivable = Invoice::where('status', '!=', 'cancelled')->get()->sum->outstanding;
        $totalCollected = Payment::where(fn ($q) => $q->where('direction', Payment::DIRECTION_RECEIPT)->orWhere('direction', 'received'))->sum('amount');

        $totalProcurements = Procurement::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalVendorPaid = Payment::where(fn ($q) => $q->where('direction', Payment::DIRECTION_PAYMENT)->orWhere('direction', 'paid'))->sum('amount');
        $totalPayable = max(0, $totalProcurements - $totalVendorPaid);

        $netWorkingCapital = $totalReceivable - $totalPayable;

        return view('admin.reports.payments.summary', compact(
            'totalReceivable',
            'totalCollected',
            'totalPayable',
            'totalVendorPaid',
            'netWorkingCapital'
        ));
    }
}

