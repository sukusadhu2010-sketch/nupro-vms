<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
                  ->orWhere('email', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
                  ->orWhere('gst_no', 'like', '%'.$request->search.'%');
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
     * Shared validation rules for customer store/update.
     * - name        = Contact Name (primary identifier, required)
     * - phone       = Mobile Number (digits only: no negatives, decimals,
     *                 spaces or non-numeric characters)
     * - gst_no      = GST No (mandatory)
     * - company     = REMOVED from the customer management form entirely
     */
    private function validateCustomer(Request $request, ?Customer $customer = null): array
    {
        $uniqueEmail = $customer
            ? Rule::unique('customers')->ignore($customer->id)
            : 'unique:customers,email';

        return $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', $uniqueEmail],
            // Digits only — rejects signs, dots, spaces, letters, symbols
            'phone' => ['nullable', 'max:15', 'regex:/^[0-9]+$/'],
            'gst_no' => 'required|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
        ]);
    }

    /**
     * Store new customer.
     */
    public function store(Request $request)
    {
        $validated = $this->validateCustomer($request);

        // Create customer independently — no User account required
        $customer = Customer::create($request->only([
            'name', 'email', 'phone', 'gst_no', 'address', 'status'
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
        $this->validateCustomer($request, $customer);

        $customer->update($request->only([
            'name', 'email', 'phone', 'gst_no', 'address', 'status'
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
     * Display the trashed (soft-deleted) customers.
     */
    public function trash()
    {
        $customers = Customer::onlyTrashed()
            ->with(['user' => function ($q) {
                $q->withTrashed();
            }])
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);

        return view('admin.customers.trash', compact('customers'));
    }

    /**
     * Restore a soft-deleted customer (and its linked user account).
     */
    public function restore(Customer $customer)
    {
        $customer->restore();

        // Restore linked user if one happens to exist (optional linkage)
        if ($customer->user) {
            $customer->user()->withTrashed()->restore();
        }

        return redirect()->route('customers.trash')
            ->with('success', 'Customer restored successfully!');
    }

    /**
     * Permanently delete a customer (only from trash).
     */
    public function forceDelete(Customer $customer)
    {
        try {
            if ($customer->user) {
                $customer->user()->forceDelete();
            }
            $customer->forceDelete();

            return redirect()->route('customers.trash')
                ->with('success', 'Customer permanently deleted!');
        } catch (\Exception $e) {
            return redirect()->route('customers.trash')
                ->with('error', 'Cannot permanently delete this customer — it is referenced by other records.');
        }
    }

    /**
     * Delete customer (soft delete) together with its linked user account.
     */
    public function destroy(Customer $customer)
    {
        // User linkage is optional — only delete the linked user if present
        if ($customer->user) {
            $customer->user->delete();
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully! You can restore it from trash.');
    }
}

