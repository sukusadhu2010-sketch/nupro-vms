<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\Procurement;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\InventoryLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VendorDashboardService
{
    /**
     * Compile all vendor management dashboard metrics and data.
     */
    public function getDashboardData(Request $request): array
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $vendorId = $request->input('vendor_id');
        $poStatus = $request->input('po_status');

        // ----------------------------------------------------
        // 1. TOP KPI SUMMARY CARDS
        // ----------------------------------------------------

        // Vendor Statistics
        $vendorStats = [
            'total' => Vendor::count(),
            'active' => Vendor::where('status', 'active')->count(),
            'inactive' => Vendor::whereIn('status', ['inactive', 'blocked'])->count(),
            'pending' => Vendor::where('status', 'pending')->count(),
            'new_this_month' => Vendor::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        // Base PO Query with Filters
        $poQuery = Procurement::query();
        if ($dateFrom) {
            $poQuery->whereDate('order_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $poQuery->whereDate('order_date', '<=', $dateTo);
        }
        if ($vendorId) {
            $poQuery->where('vendor_id', $vendorId);
        }
        if ($poStatus) {
            $poQuery->where('status', $poStatus);
        }

        // Purchase Statistics
        $totalPos = (clone $poQuery)->count();
        $openPos = (clone $poQuery)->whereIn('status', ['draft', 'pending', 'sent', 'approved', 'partially_received'])->count();
        $closedPos = (clone $poQuery)->where('status', 'received')->count();
        $cancelledPos = (clone $poQuery)->where('status', 'cancelled')->count();
        $totalPurchaseValue = (float) (clone $poQuery)->where('status', '!=', 'cancelled')->sum('total_amount');

        $purchaseStats = [
            'total_pos' => $totalPos,
            'open_pos' => $openPos,
            'closed_pos' => $closedPos,
            'cancelled_pos' => $cancelledPos,
            'total_value' => $totalPurchaseValue,
        ];

        // Financial Statistics (Vendor Payments)
        $paymentQuery = Payment::whereIn('direction', [Payment::DIRECTION_PAYMENT, 'paid']);
        if ($dateFrom) {
            $paymentQuery->whereDate('payment_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $paymentQuery->whereDate('payment_date', '<=', $dateTo);
        }
        if ($vendorId) {
            $paymentQuery->where('vendor_id', $vendorId);
        }

        $paidAmount = (float) (clone $paymentQuery)->sum('amount');
        $pendingPayments = max(0, $totalPurchaseValue - $paidAmount);
        
        $overduePayments = (float) Procurement::where('status', '!=', 'cancelled')
            ->where('status', '!=', 'received')
            ->whereDate('expected_delivery_date', '<', now())
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->sum('total_amount');

        $dueThisWeek = (float) Procurement::where('status', '!=', 'cancelled')
            ->whereBetween('expected_delivery_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->sum('total_amount');

        $dueThisMonth = (float) Procurement::where('status', '!=', 'cancelled')
            ->whereMonth('expected_delivery_date', now()->month)
            ->whereYear('expected_delivery_date', now()->year)
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->sum('total_amount');

        $financialStats = [
            'total_purchase_value' => $totalPurchaseValue,
            'paid_amount' => $paidAmount,
            'pending_payments' => $pendingPayments,
            'overdue_payments' => $overduePayments,
            'due_this_week' => $dueThisWeek,
            'due_this_month' => $dueThisMonth,
        ];

        // Accounts Receivable (Customer Invoices)
        $arPayable = [
            'vendor_total_payable' => $totalPurchaseValue,
            'vendor_paid' => $paidAmount,
            'vendor_outstanding' => $pendingPayments,
            'vendor_overdue' => $overduePayments,
            'customer_total_invoiced' => (float) Invoice::where('status', '!=', 'cancelled')->sum('grand_total'),
            'customer_received' => (float) Invoice::where('status', '!=', 'cancelled')->sum('paid_amount'),
            'customer_outstanding' => (float) Invoice::where('status', '!=', 'cancelled')->get()->sum->outstanding,
            'customer_overdue' => (float) Invoice::where('status', '!=', 'cancelled')
                ->whereIn('status', ['pending', 'partially_paid'])
                ->whereDate('invoice_date', '<', now()->subDays(30))
                ->sum('grand_total'),
        ];

        // Inventory Statistics
        $totalStockItems = Product::count();
        $stockBalances = DB::table('stock_balances')->get();
        $totalStockUnits = (float) $stockBalances->sum('quantity');
        $lowStockCount = $stockBalances->filter(fn ($b) => $b->quantity > 0 && $b->quantity <= 10)->count();
        $outOfStockCount = max(0, $totalStockItems - $stockBalances->where('quantity', '>', 0)->count());

        $inventoryValue = (float) DB::table('stock_balances')
            ->join('products', 'stock_balances.product_id', '=', 'products.id')
            ->sum(DB::raw('stock_balances.quantity * products.price'));

        $totalGrnEntries = InventoryLedger::where('transaction_type', 'grn')
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->count();

        $todayGrn = InventoryLedger::where('transaction_type', 'grn')
            ->whereDate('transaction_date', today())
            ->count();

        $thisMonthGrn = InventoryLedger::where('transaction_type', 'grn')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->count();

        $receivedMaterialsQty = (float) InventoryLedger::where('transaction_type', 'grn')
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->sum('qty_in');

        $inventoryStats = [
            'total_grns' => $totalGrnEntries,
            'today_grns' => $todayGrn,
            'this_month_grns' => $thisMonthGrn,
            'received_qty' => $receivedMaterialsQty,
            'total_items' => $totalStockItems,
            'total_units' => $totalStockUnits,
            'low_stock' => $lowStockCount,
            'out_of_stock' => $outOfStockCount,
            'inventory_value' => $inventoryValue,
            'pending_deliveries' => (clone $poQuery)->whereIn('status', ['approved', 'sent', 'partially_received'])->count(),
        ];

        // ----------------------------------------------------
        // 2. RECENT RECORDS
        // ----------------------------------------------------
        $recentPurchaseOrders = (clone $poQuery)
            ->with(['vendor', 'items.product'])
            ->latest('order_date')
            ->latest('id')
            ->take(10)
            ->get();

        $recentGrns = InventoryLedger::with(['vendor', 'product', 'purchaseOrder'])
            ->where('transaction_type', 'grn')
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->latest('transaction_date')
            ->latest('id')
            ->take(5)
            ->get();

        // ----------------------------------------------------
        // 3. VENDOR PERFORMANCE & TOP VENDORS
        // ----------------------------------------------------
        $allDeliveredPos = Procurement::where('status', 'received')->count();
        $onTimeDeliveredPos = Procurement::where('status', 'received')
            ->whereNotNull('actual_delivery_date')
            ->whereColumn('actual_delivery_date', '<=', 'expected_delivery_date')
            ->count();

        $onTimeDeliveryRate = $allDeliveredPos > 0
            ? round(($onTimeDeliveredPos / $allDeliveredPos) * 100, 1)
            : 100.0;

        $lateDeliveries = Procurement::where('status', '!=', 'cancelled')
            ->where('status', '!=', 'received')
            ->whereNotNull('expected_delivery_date')
            ->whereDate('expected_delivery_date', '<', now())
            ->count();

        $vendorPerformance = [
            'on_time_rate' => $onTimeDeliveryRate,
            'late_deliveries' => $lateDeliveries,
            'rejected_materials' => 0,
            'total_procurement_value' => $totalPurchaseValue,
        ];

        // Top 10 Vendors by Spend Volume & PO Count
        $topVendors = Vendor::withCount(['procurements as po_count' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->withSum(['procurements as total_spend' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'total_amount')
            ->orderByDesc('total_spend')
            ->take(10)
            ->get();

        // ----------------------------------------------------
        // 4. PENDING APPROVALS
        // ----------------------------------------------------
        $pendingApprovals = [
            'pos' => Procurement::with('vendor')
                ->whereIn('status', ['draft', 'pending', 'sent'])
                ->latest()
                ->take(5)
                ->get(),
            'vendors' => Vendor::where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(),
            'invoices' => Invoice::with('customer')
                ->whereIn('status', ['draft', 'pending'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        // ----------------------------------------------------
        // 5. CRITICAL ALERTS & NOTIFICATIONS
        // ----------------------------------------------------
        $alerts = [];
        if ($overduePayments > 0) {
            $alerts[] = [
                'type' => 'critical',
                'icon' => 'fa-exclamation-triangle',
                'title' => 'Overdue Vendor Deliveries / Payments',
                'message' => '₹' . number_format($overduePayments, 2) . ' worth of purchase orders are overdue past delivery target date.',
                'link' => route('procurements.index'),
            ];
        }
        if ($lateDeliveries > 0) {
            $alerts[] = [
                'type' => 'critical',
                'icon' => 'fa-clock',
                'title' => "{$lateDeliveries} Delayed Deliveries",
                'message' => "There are {$lateDeliveries} purchase orders with expected dates past due that haven't been received.",
                'link' => route('procurements.index'),
            ];
        }
        if ($lowStockCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fa-boxes',
                'title' => "{$lowStockCount} Low Stock Items",
                'message' => "Several items have reached reorder thresholds (<= 10 units in stock).",
                'link' => route('inventory-ledger.index'),
            ];
        }
        if ($vendorStats['pending'] > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'fa-user-clock',
                'title' => "{$vendorStats['pending']} New Vendor Approvals Pending",
                'message' => "New vendor registrations require review and activation.",
                'link' => route('vendors.index'),
            ];
        }
        if (empty($alerts)) {
            $alerts[] = [
                'type' => 'normal',
                'icon' => 'fa-check-circle',
                'title' => 'Procurement Operations Normal',
                'message' => 'All vendor activities, purchase orders, and inventory deliveries are on schedule.',
                'link' => '#',
            ];
        }

        // ----------------------------------------------------
        // 6. CHARTS DATA (JSON READY)
        // ----------------------------------------------------

        // Chart A: Vendor Status Doughnut
        $vendorChart = [
            'labels' => ['Active', 'Pending Approval', 'Inactive', 'Blocked'],
            'data' => [
                $vendorStats['active'],
                $vendorStats['pending'],
                Vendor::where('status', 'inactive')->count(),
                Vendor::where('status', 'blocked')->count(),
            ],
            'colors' => ['#10b981', '#f59e0b', '#6b7280', '#ef4444'],
        ];

        // Chart B: Purchase Order Status Doughnut
        $poStatusCounts = [
            'draft' => Procurement::where('status', 'draft')->count(),
            'pending' => Procurement::whereIn('status', ['pending', 'sent'])->count(),
            'approved' => Procurement::where('status', 'approved')->count(),
            'partially_received' => Procurement::where('status', 'partially_received')->count(),
            'completed' => Procurement::where('status', 'received')->count(),
            'cancelled' => Procurement::where('status', 'cancelled')->count(),
        ];
        $poChart = [
            'labels' => ['Draft', 'Pending / Sent', 'Approved', 'Partially Received', 'Completed', 'Cancelled'],
            'data' => array_values($poStatusCounts),
            'colors' => ['#9ca3af', '#3b82f6', '#8b5cf6', '#f59e0b', '#10b981', '#ef4444'],
        ];

        // Chart C: Monthly Spend Analysis (Last 6 Months)
        $monthlySpend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthLabel = $monthDate->format('M Y');
            $spendVal = (float) Procurement::where('status', '!=', 'cancelled')
                ->whereMonth('order_date', $monthDate->month)
                ->whereYear('order_date', $monthDate->year)
                ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
                ->sum('total_amount');

            $monthlySpend['labels'][] = $monthLabel;
            $monthlySpend['data'][] = $spendVal;
        }

        // Chart D: Payment Status / Mode Breakdown
        $paymentModes = [
            'Bank Transfer' => Payment::where('direction', Payment::DIRECTION_PAYMENT)->where('payment_mode', 'bank_transfer')->sum('amount'),
            'NEFT / RTGS' => Payment::where('direction', Payment::DIRECTION_PAYMENT)->whereIn('payment_mode', ['neft', 'rtgs'])->sum('amount'),
            'Cheque' => Payment::where('direction', Payment::DIRECTION_PAYMENT)->where('payment_mode', 'cheque')->sum('amount'),
            'Cash' => Payment::where('direction', Payment::DIRECTION_PAYMENT)->where('payment_mode', 'cash')->sum('amount'),
            'UPI' => Payment::where('direction', Payment::DIRECTION_PAYMENT)->where('payment_mode', 'upi')->sum('amount'),
        ];
        $paymentChart = [
            'labels' => array_keys($paymentModes),
            'data' => array_map(fn ($v) => (float) $v, array_values($paymentModes)),
            'colors' => ['#2563eb', '#06b6d4', '#84cc16', '#f97316', '#a855f7'],
        ];

        // Chart E: Inventory Movement (Last 14 Days)
        $stockMovement = ['labels' => [], 'in' => [], 'out' => []];
        for ($d = 13; $d >= 0; $d--) {
            $date = now()->subDays($d);
            $dateStr = $date->format('Y-m-d');
            $stockMovement['labels'][] = $date->format('d M');
            $stockMovement['in'][] = (float) InventoryLedger::where('transaction_type', 'grn')
                ->whereDate('transaction_date', $dateStr)
                ->sum('qty_in');
            $stockMovement['out'][] = (float) InventoryLedger::where('transaction_type', 'issue')
                ->whereDate('transaction_date', $dateStr)
                ->sum('qty_out');
        }

        // ----------------------------------------------------
        // 7. VENDOR-WISE PURCHASE ANALYSIS (TABLE)
        // ----------------------------------------------------
        $vendorAnalysis = Vendor::withCount(['procurements as total_pos' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->withSum(['procurements as total_spend' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'total_amount')
            ->withSum(['payments as total_paid' => fn ($q) => $q->where('direction', Payment::DIRECTION_PAYMENT)], 'amount')
            ->orderByDesc('total_spend')
            ->get()
            ->map(function ($v) {
                $spend = (float) ($v->total_spend ?? 0);
                $paid = (float) ($v->total_paid ?? 0);
                $outstanding = max(0, $spend - $paid);
                $lastPo = Procurement::where('vendor_id', $v->id)->latest('order_date')->value('order_date');

                return [
                    'id' => $v->id,
                    'name' => $v->name,
                    'vendor_type' => $v->vendor_type ?? 'Vendor',
                    'status' => $v->status,
                    'po_count' => $v->total_pos ?? 0,
                    'total_spend' => $spend,
                    'total_paid' => $paid,
                    'outstanding' => $outstanding,
                    'last_po_date' => $lastPo ? \Carbon\Carbon::parse($lastPo)->format('d M Y') : '—',
                ];
            });

        // Filter Dropdowns
        $allVendors = Vendor::orderBy('name')->get(['id', 'name']);

        return compact(
            'vendorStats',
            'purchaseStats',
            'financialStats',
            'arPayable',
            'inventoryStats',
            'recentPurchaseOrders',
            'recentGrns',
            'vendorPerformance',
            'topVendors',
            'pendingApprovals',
            'alerts',
            'vendorChart',
            'poChart',
            'monthlySpend',
            'paymentChart',
            'stockMovement',
            'vendorAnalysis',
            'allVendors',
            'dateFrom',
            'dateTo',
            'vendorId',
            'poStatus'
        );
    }

    /**
     * Export Vendor-Wise Purchase Analysis to CSV.
     */
    public function exportVendorAnalysis(): StreamedResponse
    {
        $data = Vendor::withCount(['procurements as total_pos' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->withSum(['procurements as total_spend' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'total_amount')
            ->withSum(['payments as total_paid' => fn ($q) => $q->where('direction', Payment::DIRECTION_PAYMENT)], 'amount')
            ->orderByDesc('total_spend')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Vendor_Purchase_Analysis_' . date('Y-m-d') . '.csv"',
        ];

        return new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Vendor Name',
                'Vendor Type',
                'Status',
                'PO Count',
                'Purchase Value (INR)',
                'Paid Amount (INR)',
                'Outstanding Balance (INR)',
                'Last Order Date',
            ]);

            foreach ($data as $v) {
                $spend = (float) ($v->total_spend ?? 0);
                $paid = (float) ($v->total_paid ?? 0);
                $outstanding = max(0, $spend - $paid);
                $lastPo = Procurement::where('vendor_id', $v->id)->latest('order_date')->value('order_date');

                fputcsv($handle, [
                    $v->name,
                    $v->vendor_type ?? 'Vendor',
                    ucfirst($v->status),
                    $v->total_pos ?? 0,
                    number_format($spend, 2, '.', ''),
                    number_format($paid, 2, '.', ''),
                    number_format($outstanding, 2, '.', ''),
                    $lastPo ? \Carbon\Carbon::parse($lastPo)->format('Y-m-d') : '—',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}

