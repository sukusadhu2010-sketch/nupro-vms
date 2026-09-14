<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationSettingController extends Controller
{
    public function edit()
    {
        $organization = OrganizationSetting::current();
        return view('admin.organization.edit', compact('organization'));
    }

    public function update(Request $request)
    {
        $organization = OrganizationSetting::current();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|alpha_num|max:6|min:1',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'remove_logo' => 'nullable|boolean',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'gstin' => 'nullable|string|max:30',
            'contact_number' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'bank_details' => 'nullable|string|max:2000',
        ]);

        $validated['abbreviation'] = strtoupper($validated['abbreviation']);

        if ($request->boolean('remove_logo') && $organization->logo_path) {
            Storage::disk('public')->delete($organization->logo_path);
            $validated['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($organization->logo_path) {
                Storage::disk('public')->delete($organization->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('organization', 'public');
        }

        $organization->fill($validated)->save();

        return redirect()->route('organization.edit')
            ->with('success', 'Organization settings updated successfully!');
    }
}
