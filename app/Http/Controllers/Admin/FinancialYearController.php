<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\FinancialYear;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinancialYearController extends Controller
{
    public function index()
    {
        $years = FinancialYear::orderBy('start_date', 'desc')->get();
        return view('admin.financial-years.index', compact('years'));
    }

    public function create()
    {
        $suggest = FinancialYear::suggestFromDate(now());
        return view('admin.financial-years.create', compact('suggest'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $this->validateNoOverlap($validated);

        $fy = FinancialYear::create($validated);

        if ($request->boolean('activate')) {
            $fy->activate();
        }

        return redirect()->route('financial-years.index')->with('success', 'Financial year created.');
    }

    public function edit(FinancialYear $financial_year)
    {
        $fy = $financial_year;
        // Lock the short code once documents exist against this FY
        $codeLocked = $fy->hasDocuments();
        return view('admin.financial-years.edit', compact('fy', 'codeLocked'));
    }

    public function update(Request $request, FinancialYear $financial_year)
    {
        $validated = $this->validated($request, $financial_year->id);
        $this->validateNoOverlap($validated, $financial_year->id);

        $fy = $financial_year;
        $wasActive = $fy->is_active;

        $fy->update($validated);

        // Handle activation request from edit form
        if ($request->boolean('activate') && !$fy->is_active) {
            $fy->activate();
        }

        return redirect()->route('financial-years.index')->with('success', 'Financial year updated.');
    }

    public function activate(FinancialYear $financial_year)
    {
        $financial_year->activate();
        return redirect()->route('financial-years.index')
            ->with('success', "FY {$financial_year->fy_label} is now active. Serial counters for new documents are scoped to this year.");
    }

    public function destroy(FinancialYear $financial_year)
    {
        if ($financial_year->is_active) {
            return redirect()->route('financial-years.index')
                ->with('error', 'Cannot delete the active financial year. Activate another one first.');
        }

        if ($financial_year->hasDocuments()) {
            return redirect()->route('financial-years.index')
                ->with('error', "FY {$financial_year->fy_label} already has quotations/enquiries linked to it and cannot be deleted.");
        }

        $financial_year->delete();
        return redirect()->route('financial-years.index')->with('success', 'Financial year deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'fy_label' => ['required', 'string', 'max:20', Rule::unique('financial_years', 'fy_label')->ignore($ignoreId)],
            'fy_short_code' => ['required', 'string', 'max:10', 'regex:/^\d{2}-\d{2}$/', Rule::unique('financial_years', 'fy_short_code')->ignore($ignoreId)],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);
    }

    private function validateNoOverlap(array $data, ?int $ignoreId = null): void
    {
        $overlap = FinancialYear::where('id', '!=', $ignoreId)
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_date', [$data['start_date'], $data['end_date']])
                    ->orWhereBetween('end_date', [$data['start_date'], $data['end_date']])
                    ->orWhere(function ($q2) use ($data) {
                        $q2->where('start_date', '<=', $data['start_date'])
                            ->where('end_date', '>=', $data['end_date']);
                    });
            })->exists();

        if ($overlap) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'start_date' => 'Date range overlaps with an existing financial year.',
            ]);
        }
    }
}
