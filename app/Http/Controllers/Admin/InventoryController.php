<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {
    }

    /**
     * Display inventory list with optional date filtering.
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $type = $request->get('type');
        
        // Get inventories with their current stock levels
        $inventories = Inventory::with('product')
            ->orderBy('quantity', 'asc')
            ->get();
        
        // Get transactions for the date range
        $transactions = $this->inventoryService->getTransactionsByDate(
            $startDate,
            $endDate,
            $type
        );
        
        // Get summary
        $summary = $this->inventoryService->getSummaryByDate($startDate ?? now()->toDateString());
        
        // Get low stock products
        $lowStockProducts = $this->inventoryService->getProductsNeedingReorder();
        
        // Get inventory value
        $inventoryValue = $this->inventoryService->getInventoryValue();
        
        return view('admin.inventories.index', compact(
            'inventories',
            'transactions',
            'summary',
            'lowStockProducts',
            'inventoryValue',
            'startDate',
            'endDate',
            'type'
        ));
    }

    /**
     * Display inventory details for a specific product.
     */
    public function show(Inventory $inventory)
    {
        $inventory->load(['product', 'transactions.user']);
        
        // Get transactions for this inventory
        $transactions = InventoryTransaction::where('inventory_id', $inventory->id)
            ->with(['user'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.inventories.show', compact('inventory', 'transactions'));
    }

    /**
     * Display inventory transactions by date.
     */
    public function transactions(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $type = $request->get('type');
        
        $transactions = $this->inventoryService->getTransactionsByDate(
            $startDate,
            $endDate,
            $type
        );
        
        // Group by date
        $groupedTransactions = $transactions->groupBy(function ($item) {
            return $item->transaction_date->format('Y-m-d');
        });
        
        // Summary for date range
        $rangeSummary = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_inflow' => $transactions->where('transaction_type', 'inflow')->sum('quantity'),
            'total_outflow' => $transactions->where('transaction_type', 'outflow')->sum('quantity'),
        ];
        
        return view('admin.inventories.transactions', compact(
            'transactions',
            'groupedTransactions',
            'rangeSummary',
            'startDate',
            'endDate',
            'type'
        ));
    }

    /**
     * Get low stock alerts.
     */
    public function alerts()
    {
        $lowStockProducts = $this->inventoryService->getProductsNeedingReorder();
        
        return view('admin.inventories.alerts', compact('lowStockProducts'));
    }

    /**
     * Get inventory dashboard summary.
     */
    public function dashboard()
    {
        $inventoryValue = $this->inventoryService->getInventoryValue();
        $todaySummary = $this->inventoryService->getSummaryByDate(now()->toDateString());
        $lowStockProducts = $this->inventoryService->getProductsNeedingReorder();
        
        // Recent transactions (last 7 days)
        $recentTransactions = $this->inventoryService->getTransactionsByDate(
            now()->subDays(7)->toDateString(),
            now()->toDateString()
        );
        
        return view('admin.inventories.dashboard', compact(
            'inventoryValue',
            'todaySummary',
            'lowStockProducts',
            'recentTransactions'
        ));
    }
}
