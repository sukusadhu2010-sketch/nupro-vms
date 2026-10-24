<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\VendorDashboardService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    protected VendorDashboardService $dashboardService;

    public function __construct(VendorDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display interactive Vendor Management Dashboard for ERP staff / management,
     * or dedicated portals for external role types.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('vendor')) {
            $data = [
                'user' => $user,
                'vendor' => $user->vendor,
                'orders' => [],
                'stats' => [
                    'total_orders' => 0,
                    'revenue' => 0,
                    'rating' => 4.5,
                ],
            ];
            return view('dashboard.vendor', $data);
        }

        if ($user->hasRole('customer')) {
            $data = [
                'user' => $user,
                'customer' => $user->customer,
                'vendors' => \App\Models\Vendor::where('status', 'active')->take(10)->get(),
                'stats' => [
                    'total_orders' => 0,
                    'saved_vendors' => 3,
                ],
            ];
            return view('dashboard.customer', $data);
        }

        // All internal ERP users (Administrator, Purchase, Accounts, Stores, Management, etc.)
        $data = $this->dashboardService->getDashboardData($request);
        $data['user'] = $user;

        return view('dashboard.vendor-management', $data);
    }

    /**
     * Export Vendor-Wise Purchase Analysis to CSV
     */
    public function exportVendors(): StreamedResponse
    {
        return $this->dashboardService->exportVendorAnalysis();
    }
}
