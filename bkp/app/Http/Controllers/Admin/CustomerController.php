<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $query = Customer::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('company', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->paginate(15);

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show customer create form.
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store new customer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'company' => 'required|string|max:255',
            'address' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('customer123'), // Default, force change
        ]);

        // Assign customer role (assuming role_id = 3)
        $customerRole = Role::where('name', 'customer')->first();
        if ($customerRole) {
            $user->roles()->attach($customerRole->id);
        }

        // Create customer
        $customer = $user->customer()->create($request->only([
            'name', 'email', 'phone', 'company', 'address', 'status'
        ]));

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully!');
    }

    /**
     * Display customer details.
     */
    public function show(Customer $customer)
    {
        $customer->load('user');
        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Show customer edit form.
     */
    public function edit(Customer $customer)
    {
        $customer->load('user');
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($customer->user_id)],
            'phone' => 'nullable|string|max:20',
            'company' => 'required|string|max:255',
            'address' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
        ]);

        $customer->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $customer->update($request->only([
            'phone', 'company', 'address', 'status'
        ]));

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully!');
    }

    /**
     * Toggle customer status (AJAX support).
     */
    public function toggleStatus(Request $request, Customer $customer)
    {
        $newStatus = $customer->status === 'active' ? 'suspended' : 'active';
        $customer->update(['status' => $newStatus]);

        $badgeClass = $newStatus === 'active' ? 'bg-success' : 'bg-danger';
        $badgeIcon = $newStatus === 'active' ? 'fa-check-circle' : 'fa-ban';
        $badgeText = ucfirst($newStatus);

        $statusBadge = '<span class="badge ' . $badgeClass . ' px-3 py-2 rounded-pill">
            <i class="fas ' . $badgeIcon . ' me-1"></i>' . $badgeText . '
        </span>';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status_badge' => $statusBadge,
                'message' => 'Status updated successfully'
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer status updated!');
    }

    /**
     * Delete customer.
     */
    public function destroy(Customer $customer)
    {
        $customer->user->delete();
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully!');
    }
}

