<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountsDashboardController extends Controller
{
    public function index()
    {
        // 1. Receivables
        $invoices = Invoice::where('status', '!=', 'cancelled')->get();
        $totalReceivable = $invoices->sum->outstanding;
        $overdueReceivables = $invoices->filter(fn ($inv) => $inv->outstanding > 0 && now()->diffInDays($inv->invoice_date) > 30)
            ->sum->outstanding;

        // 2. Payables (from POs / Procurements)
        $procurements = Procurement::where('status', '!=', 'cancelled')->get();
        $totalPoAmount = (float) $procurements->sum('total_amount');
        $totalVendorPaid = (float) Payment::where(fn ($q) => $q->where('direction', Payment::DIRECTION_PAYMENT)->orWhere('direction', 'paid'))->sum('amount');
        $totalPayable = max(0, $totalPoAmount - $totalVendorPaid);
        $overduePayables = $procurements->filter(fn ($po) => now()->diffInDays($po->order_date) > 30)
            ->sum('total_amount') - $totalVendorPaid;
        $overduePayables = max(0, $overduePayables);

        // 3. Recent Customer Receipts
        $recentReceipts = Payment::with('customer')
            ->where(fn ($q) => $q->where('direction', Payment::DIRECTION_RECEIPT)->orWhere('direction', 'received'))
            ->latest('payment_date')
            ->take(5)
            ->get();

        // 4. Recent Vendor Payments
        $recentPayments = Payment::with(['vendor', 'purchaseOrder'])
            ->where(fn ($q) => $q->where('direction', Payment::DIRECTION_PAYMENT)->orWhere('direction', 'paid'))
            ->latest('payment_date')
            ->take(5)
            ->get();

        // 5. Monthly Cash Inflow vs Outflow
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('M Y');
            $inflow = (float) Payment::where(fn ($q) => $q->where('direction', Payment::DIRECTION_RECEIPT)->orWhere('direction', 'received'))
                ->whereMonth('payment_date', $date->month)
                ->whereYear('payment_date', $date->year)
                ->sum('amount');
            $outflow = (float) Payment::where(fn ($q) => $q->where('direction', Payment::DIRECTION_PAYMENT)->orWhere('direction', 'paid'))
                ->whereMonth('payment_date', $date->month)
                ->whereYear('payment_date', $date->year)
                ->sum('amount');

            $months[] = [
                'month' => $monthKey,
                'inflow' => $inflow,
                'outflow' => $outflow,
            ];
        }

        return view('admin.accounts.dashboard', compact(
            'totalReceivable',
            'totalPayable',
            'overdueReceivables',
            'overduePayables',
            'recentReceipts',
            'recentPayments',
            'months'
        ));
    }
}

