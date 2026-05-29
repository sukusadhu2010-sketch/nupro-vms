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
            $query->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('company', 'like', '%'.$request->search.'%');
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'company' => 'required|string|max:255',
            'specialization' => 'required|string|max:255',
            'address' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('vendor123'), // Default, force change
        ]);

        // Create vendor
        $vendor = $user->vendor()->create($request->only([
           'name','email', 'phone', 'company', 'specialization', 'address', 'status'
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
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($vendor->user_id)],
            'phone' => 'nullable|string|max:20',
            'company' => 'required|string|max:255',
            'specialization' => 'required|string|max:255',
            'address' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
        ]);

        $vendor->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $vendor->update($request->only([
            'phone', 'company', 'specialization', 'address', 'status'
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

