<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class VendorController extends Controller
{
    /**
     * Resolve the vendor type handled by the current route group
     * (foundry-vendors.* → FOUNDRY, sub-vendors.* → SUB VENDOR).
     * Returns [type, typeParam] where typeParam is 'foundry' or 'sub'.
     */
    protected function typeContext(): array
    {
        $name = request()->route()?->getName() ?? '';

        if (str_starts_with($name, 'sub-vendors.') || str_starts_with($name, 'sub.')) {
            return ['SUB VENDOR', 'sub'];
        }

        return ['FOUNDRY', 'foundry'];
    }

    /**
     * Display a listing of vendors.
     */
    public function index(Request $request)
    {
        [$type, $typeParam] = $this->typeContext();

        $query = Vendor::with('user')
            ->where('vendor_type', $type)
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                // "Contact Name" is the primary identifier for the vendor
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('particulars', 'like', '%' . $request->search . '%')
                  ->orWhere('gst_no', 'like', '%' . $request->search . '%');
            });
        }


        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vendors = $query->paginate(15);

        $view = $type === 'SUB VENDOR' ? 'admin.vendors.sub.index' : 'admin.vendors.foundry.index';
        return view($view, compact('vendors', 'type', 'typeParam'));
    }

    /**
     * Show vendor create form.
     */
    public function create()
    {
        [$type, $typeParam] = $this->typeContext();

        $view = $type === 'SUB VENDOR' ? 'admin.vendors.sub.create' : 'admin.vendors.foundry.create';
        return view($view, compact('type', 'typeParam'));
    }

    /**
     * Store new vendor.
     */
    public function store(Request $request)
    {
        [$type, $typeParam] = $this->typeContext();

        $request->validate([
            // "Contact Name" — primary identifier for the vendor
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            // "Mobile Number" — digits only, no signs/decimals/spaces/special chars
            'phone' => ['nullable', 'digits_between:10,15', 'regex:/^[0-9]+$/'],
            'particulars' => 'nullable|string|max:255',
            // "Vendor Type" — forced by the management section, not user input
            'vendor_type' => ['required', 'string', Rule::in(Vendor::VENDOR_TYPES)],
            // "GST No" — mandatory, validated before saving
            'gst_no' => ['required', 'string', 'max:20', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][0-9A-Z]Z[0-9A-Z]$/'],
            'address' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
        ], [
            'phone.regex' => 'Mobile Number must contain digits only (no spaces, symbols, decimals or negative values).',
            'phone.digits_between' => 'Mobile Number must be 10 to 15 digits.',
            'gst_no.required' => 'GST No is mandatory.',
            'gst_no.regex' => 'GST No must be a valid GSTIN (e.g. 22AAAAA0000A1Z5).',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('vendor123'), // Default, force change
        ]);

        // Create vendor — vendor_type comes from the route group, not the form
        $vendor = $user->vendor()->create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'particulars' => $request->particulars,
            'vendor_type' => $type,
            'address' => $request->address,
            'status' => $request->status,
            'gst_no' => $request->gst_no,
        ]);

        return redirect()->route($typeParam . '-vendors.index')
            ->with('success', ($type === 'SUB VENDOR' ? 'Sub Vendor' : 'Foundry') . ' created successfully!');
    }

    /**
     * Display vendor details.
     */
    public function show(Vendor $vendor)
    {
        [$type, $typeParam] = $this->typeContext();

        $view = $type === 'SUB VENDOR' ? 'admin.vendors.sub.show' : 'admin.vendors.foundry.show';
        return view($view, compact('vendor', 'type', 'typeParam'));
    }

    /**
     * Show vendor edit form.
     */
    public function edit(Vendor $vendor)
    {
        [$type, $typeParam] = $this->typeContext();

        $view = $type === 'SUB VENDOR' ? 'admin.vendors.sub.edit' : 'admin.vendors.foundry.edit';
        return view($view, compact('vendor', 'type', 'typeParam'));
    }

    /**
     * Update vendor.
     */
    public function update(Request $request, Vendor $vendor)
    {
        [$type, $typeParam] = $this->typeContext();

        $request->validate([
            // "Contact Name" — primary identifier for the vendor
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($vendor->user_id)],
            // "Mobile Number" — digits only, no signs/decimals/spaces/special chars
            'phone' => ['nullable', 'digits_between:10,15', 'regex:/^[0-9]+$/'],
            'particulars' => 'nullable|string|max:255',
            // "Vendor Type" — fixed by the management section
            'vendor_type' => ['required', 'string', Rule::in(Vendor::VENDOR_TYPES)],
            // "GST No" — mandatory, validated before saving
            'gst_no' => ['required', 'string', 'max:20', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][0-9A-Z]Z[0-9A-Z]$/'],
            'address' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
        ], [
            'phone.regex' => 'Mobile Number must contain digits only (no spaces, symbols, decimals or negative values).',
            'phone.digits_between' => 'Mobile Number must be 10 to 15 digits.',
            'gst_no.required' => 'GST No is mandatory.',
            'gst_no.regex' => 'GST No must be a valid GSTIN (e.g. 22AAAAA0000A1Z5).',
        ]);

        $vendor->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $vendor->update([
            'phone' => $request->phone,
            'particulars' => $request->particulars,
            'address' => $request->address,
            'status' => $request->status,
            'gst_no' => $request->gst_no,
            // vendor_type is NOT updated — it is fixed by the management section
        ]);

        return redirect()->route($typeParam . '-vendors.index')
            ->with('success', 'Vendor updated successfully!');
    }

    /**
     * Toggle vendor status.
     */
    public function toggleStatus(Request $request, Vendor $vendor)
    {
        try {
            $newStatus = $vendor->status === 'active' ? 'suspended' : 'active';
            $updated = $vendor->update(['status' => $newStatus]);

            if ($updated) {
                return response()->json(['status' => true, 'new_status' => $newStatus, 'message' => 'Vendor status updated successfully!']);
            } else {
                return response()->json(['status' => false, 'new_status' => $newStatus, 'message' => 'Failed to update vendor status.']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'new_status' => '', 'message' => 'Something went wrong.']);
        }
    }

    /**
     * Delete vendor.
     */
    public function destroy(Vendor $vendor)
    {
        try {
            $vendor->user->delete();
            $vendor->delete();
            return response()->json(['status' => true,'message' => 'Vendor deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => false,'message' => 'Failed to delete vendor.']);
        }

        // return redirect()->route('vendors.index')
        //     ->with('success', 'Vendor deleted successfully!');
    }
}

