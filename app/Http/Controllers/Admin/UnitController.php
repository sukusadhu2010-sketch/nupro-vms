<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    /**
     * Display a listing of units.
     */
    public function index(Request $request)
    {
        $query = Unit::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('symbol', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $units = $query->paginate(15);

        return view('admin.units.index', compact('units'));
    }

    /**
     * Show unit create form.
     */
    public function create()
    {
        return view('admin.units.create');
    }

    /**
     * Store new unit.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|unique:units,symbol|max:20',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        Unit::create($request->only(['name', 'symbol', 'description', 'status']));

        return redirect()->route('units.index')
            ->with('success', 'Unit created successfully!');
    }

    /**
     * Display unit details.
     */
    public function show(Unit $unit)
    {
        $unit->load('products');
        return view('admin.units.show', compact('unit'));
    }

    /**
     * Show unit edit form.
     */
    public function edit(Unit $unit)
    {
        return view('admin.units.edit', compact('unit'));
    }

    /**
     * Update unit.
     */
    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => ['required', 'string', 'max:20', Rule::unique('units', 'symbol')->ignore($unit)],
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $unit->update($request->only(['name', 'symbol', 'description', 'status']));

        return redirect()->route('units.index')
            ->with('success', 'Unit updated successfully!');
    }

    /**
     * Toggle unit status (AJAX).
     */
    public function toggleStatus(Request $request, Unit $unit)
    {
        $newStatus = $unit->status === 'active' ? 'inactive' : 'active';
        $unit->update(['status' => $newStatus]);

        $badgeClass = $newStatus === 'active' ? 'bg-success' : 'bg-danger';
        $badgeIcon = $newStatus === 'active' ? 'fa-check-circle' : 'fa-ban';
        $badgeText = ucfirst($newStatus);

        $statusBadge = '<span class="badge ' . $badgeClass . ' px-3 py-2">
            <i class="fas ' . $badgeIcon . ' me-1"></i>' . $badgeText . '
        </span>';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status_badge' => $statusBadge,
            ]);
        }

        return redirect()->route('units.index')
            ->with('success', 'Unit status updated!');
    }

    /**
     * Delete unit.
     */
    public function destroy(Unit $unit)
    {
        try {
            $unit->delete();
            return response()->json(['status' => true, 'message' => 'Unit deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to delete unit.']);
        }
    }
}

