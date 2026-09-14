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
     * Display a listing of vendors.
     */
    public function index(Request $request)
    {
        $query = Vendor::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                // "Contact Name" is the primary identifier for the vendor
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('particulars', 'like', '%' . $request->search . '%')
                  ->orWhere('gst_no', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('vendor_type')) {
            $query->where('vendor_type', $request->vendor_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vendors = $query->paginate(15);

        return view('admin.vendors.index', compact('vendors'));
    }

    /**
     * Show vendor create form.
     */
    public function create()
    {
        return view('admin.vendors.create');
    }

    /**
     * Store new vendor.
     */
    public function store(Request $request)
    {
        $request->validate([
            // "Contact Name" — primary identifier for the vendor
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            // "Mobile Number" — digits only, no signs/decimals/spaces/special chars
            'phone' => ['nullable', 'digits_between:10,15', 'regex:/^[0-9]+$/'],
            'particulars' => 'nullable|string|max:255',
            // "Vendor Type" — mandatory, FOUNDRY or SUB VENDOR
            'vendor_type' => ['required', 'string', Rule::in(Vendor::VENDOR_TYPES)],
            // "GST No" — mandatory, validated before saving
            'gst_no' => ['required', 'string', 'max:20', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][0-9A-Z]Z[0-9A-Z]$/'],
            'address' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
        ], [
            'phone.regex' => 'Mobile Number must contain digits only (no spaces, symbols, decimals or negative values).',
            'phone.digits_between' => 'Mobile Number must be 10 to 15 digits.',
            'vendor_type.required' => 'Vendor Type is mandatory — please select FOUNDRY or SUB VENDOR.',
            'vendor_type.in' => 'Vendor Type must be either FOUNDRY or SUB VENDOR.',
            'gst_no.required' => 'GST No is mandatory.',
            'gst_no.regex' => 'GST No must be a valid GSTIN (e.g. 22AAAAA0000A1Z5).',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('vendor123'), // Default, force change
        ]);

        // Create vendor
        $vendor = $user->vendor()->create($request->only([
            'name', 'email', 'phone', 'particulars', 'vendor_type', 'address', 'status', 'gst_no'
        ]));

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor created successfully!');
    }

    /**
     * Display vendor details.
     */
    public function show(Vendor $vendor)
    {
        return view('admin.vendors.show', compact('vendor'));
    }

    /**
     * Show vendor edit form.
     */
    public function edit(Vendor $vendor)
    {
        return view('admin.vendors.edit', compact('vendor'));
    }

    /**
     * Update vendor.
     */
    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            // "Contact Name" — primary identifier for the vendor
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($vendor->user_id)],
            // "Mobile Number" — digits only, no signs/decimals/spaces/special chars
            'phone' => ['nullable', 'digits_between:10,15', 'regex:/^[0-9]+$/'],
            'particulars' => 'nullable|string|max:255',
            // "Vendor Type" — mandatory, FOUNDRY or SUB VENDOR
            'vendor_type' => ['required', 'string', Rule::in(Vendor::VENDOR_TYPES)],
            // "GST No" — mandatory, validated before saving
            'gst_no' => ['required', 'string', 'max:20', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][0-9A-Z]Z[0-9A-Z]$/'],
            'address' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
        ], [
            'phone.regex' => 'Mobile Number must contain digits only (no spaces, symbols, decimals or negative values).',
            'phone.digits_between' => 'Mobile Number must be 10 to 15 digits.',
            'vendor_type.required' => 'Vendor Type is mandatory — please select FOUNDRY or SUB VENDOR.',
            'vendor_type.in' => 'Vendor Type must be either FOUNDRY or SUB VENDOR.',
            'gst_no.required' => 'GST No is mandatory.',
            'gst_no.regex' => 'GST No must be a valid GSTIN (e.g. 22AAAAA0000A1Z5).',
        ]);

        $vendor->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $vendor->update($request->only([
            'phone', 'particulars', 'vendor_type', 'address', 'status', 'gst_no'
        ]));

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor updated successfully!');
    }

    /**
     * Toggle vendor status.
     */
    public function toggleStatus(Vendor $vendor)
    {
        try {
            //code...

        $newStatus = $vendor->status === 'active' ? 'suspended' : 'active';
        $vendor->update(['status' => $newStatus]);
        if($vendor->update(['status' => $newStatus])){
            return response()->json(['status' => true, 'new_status' => $newStatus,'message' => 'Vendor status updated successfully!']);
        }else{
              return response()->json(['status' => false, 'new_status' => $newStatus, 'message' => 'Failed to update vendor status.']);
        }
         } catch (\Throwable $th) {
              return response()->json(['status' => false, 'new_status' => '', 'message' => 'Something went wrong.']);
        }
        // return redirect()->route('vendors.index')
        //     ->with('success', 'Vendor status updated!');
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

