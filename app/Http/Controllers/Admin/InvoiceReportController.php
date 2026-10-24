<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceReportController extends Controller
{
    /**
     * Report 1: Invoice Register
     */
    public function invoiceRegister(Request $request)
    {
        $query = Invoice::with('customer')->orderByDesc('invoice_date');

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(30)->withQueryString();
        $customers = Customer::orderBy('name')->get(['id', 'name']);

        $totals = [
            'taxable' => (clone $query)->sum('taxable_amount'),
            'cgst' => (clone $query)->sum('cgst'),
            'sgst' => (clone $query)->sum('sgst'),
            'igst' => (clone $query)->sum('igst'),
            'grand_total' => (clone $query)->sum('grand_total'),
            'paid_amount' => (clone $query)->sum('paid_amount'),
        ];

        return view('admin.reports.invoices.register', compact('invoices', 'customers', 'totals'));
    }

    /**
     * Report 2: Outstanding Report
     */
    public function outstandingReport(Request $request)
    {
        $query = Invoice::with('customer')
            ->where('status', '!=', 'cancelled')
            ->whereRaw('grand_total > paid_amount')
            ->orderBy('invoice_date');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $invoices = $query->get()->map(function ($inv) {
            $days = now()->diffInDays($inv->invoice_date);
            $inv->aging_days = $days;
            $inv->aging_bucket = match (true) {
                $days <= 30 => '0-30 days',
                $days <= 60 => '31-60 days',
                $days <= 90 => '61-90 days',
                default => '90+ days',
            };
            return $inv;
        });

        $customers = Customer::orderBy('name')->get(['id', 'name']);
        $totalOutstanding = $invoices->sum(fn ($i) => (float) $i->outstanding);

        return view('admin.reports.invoices.outstanding', compact('invoices', 'customers', 'totalOutstanding'));
    }

    /**
     * Report 3: Tax Summary (GST summary)
     */
    public function taxSummary(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Query invoice items joined with invoices
        $taxBreakup = InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.status', '!=', 'cancelled')
            ->whereDate('invoices.invoice_date', '>=', $dateFrom)
            ->whereDate('invoices.invoice_date', '<=', $dateTo)
            ->select(
                'invoice_items.tax_percent',
                DB::raw('SUM(invoice_items.quantity * invoice_items.rate - invoice_items.discount_amount) as total_taxable'),
                DB::raw('SUM(invoice_items.tax_amount) as total_tax'),
                DB::raw('COUNT(DISTINCT invoices.id) as invoice_count')
            )
            ->groupBy('invoice_items.tax_percent')
            ->orderBy('invoice_items.tax_percent')
            ->get();

        $overallInvoices = Invoice::where('status', '!=', 'cancelled')
            ->whereDate('invoice_date', '>=', $dateFrom)
            ->whereDate('invoice_date', '<=', $dateTo);

        $totals = [
            'cgst' => (clone $overallInvoices)->sum('cgst'),
            'sgst' => (clone $overallInvoices)->sum('sgst'),
            'igst' => (clone $overallInvoices)->sum('igst'),
            'cess' => (clone $overallInvoices)->sum('cess'),
            'grand_total' => (clone $overallInvoices)->sum('grand_total'),
        ];

        return view('admin.reports.invoices.tax-summary', compact('taxBreakup', 'totals', 'dateFrom', 'dateTo'));
    }

    /**
     * Report 4: Customer Ledger
     */
    public function customerLedger(Request $request)
    {
        $customers = Customer::orderBy('name')->get(['id', 'name']);
        $selectedCustomer = null;
        $ledgerEntries = collect();
        $summary = ['total_debit' => 0, 'total_credit' => 0, 'balance' => 0];

        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::find($request->customer_id);

            if ($selectedCustomer) {
                // Fetch invoices (debits)
                $invoices = Invoice::where('customer_id', $selectedCustomer->id)
                    ->where('status', '!=', 'cancelled')
                    ->select('invoice_date as date', 'invoice_number as reference', DB::raw('"Invoice" as type'), 'grand_total as debit', DB::raw('0 as credit'), 'created_at')
                    ->get();

                // Fetch receipts (credits)
                $receipts = Payment::where('customer_id', $selectedCustomer->id)
                    ->where(function ($q) {
                        $q->where('direction', 'receipt')->orWhere('direction', 'received');
                    })
                    ->select('payment_date as date', 'payment_number as reference', DB::raw('"Receipt" as type'), DB::raw('0 as debit'), 'amount as credit', 'created_at')
                    ->get();

                $merged = $invoices->concat($receipts)->sortBy('date');

                $runningBalance = 0;
                $ledgerEntries = $merged->map(function ($row) use (&$runningBalance) {
                    $runningBalance += (float) $row->debit - (float) $row->credit;
                    $row->balance = $runningBalance;
                    return $row;
                });

                $summary['total_debit'] = $invoices->sum('debit');
                $summary['total_credit'] = $receipts->sum('credit');
                $summary['balance'] = $runningBalance;
            }
        }

        return view('admin.reports.invoices.customer-ledger', compact('customers', 'selectedCustomer', 'ledgerEntries', 'summary'));
    }
}

