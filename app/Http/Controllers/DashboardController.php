<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Role;

class DashboardController extends Controller
{
    /**
     * Display role-based dashboard.
     */
    public function dashboard(Request $request): View
    {
        $user = Auth::user();

        $data = [
            'user' => $user,
        ];

        if ($user->hasRole('admin')) {
            // Admin Dashboard Data
            $data['stats'] = [
                'total_users' => User::count(),
                'total_vendors' => Vendor::count(),
                'total_customers' => Customer::count(),
                'total_roles' => Role::count(),
                'pending_vendors' => Vendor::where('status', 'pending')->count(),
                'active_customers' => Customer::where('status', 'active')->count(),
            ];
            $data['recent_users'] = User::with('roles')->latest()->take(10)->get();
            $data['recent_vendors'] = Vendor::with('user')->latest()->take(5)->get();
            return view('dashboard.admin', $data);
        }

        if ($user->hasRole('vendor')) {
            // Vendor Dashboard Data
            $data['vendor'] = $user->vendor;
            $data['orders'] = []; // Future orders model
            $data['stats'] = [
                'total_orders' => 0,
                'revenue' => 0,
                'rating' => 4.5,
            ];
            return view('dashboard.vendor', $data);
        }

        if ($user->hasRole('customer')) {
            // Customer Dashboard Data
            $data['customer'] = $user->customer;
            $data['vendors'] = Vendor::where('status', 'active')->take(10)->get();
            $data['stats'] = [
                'total_orders' => 0,
                'saved_vendors' => 3,
            ];
            return view('dashboard.customer', $data);
        }

        // Default dashboard
        return view('dashboard', $data);
    }
}

