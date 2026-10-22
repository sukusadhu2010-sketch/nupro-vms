<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankGuarantee;
use App\Services\SdPbgExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class SdPbgRegisterController extends Controller
{
    /**
     * Pull & normalise filter inputs from the request.
     */
    private function extractFilters(Request $request): array
    {
        return array_filter([
            'client'      => $request->input('client'),
            'bg_number'   => $request->input('bg_number'),
            'po_no'       => $request->input('po_no'),
            'invoice_no'  => $request->input('invoice_no'),
            'status'      => $request->input('status') === 'all' ? null : $request->input('status'),
            'bg_date_from' => $request->input('bg_date_from'),
            'bg_date_to'   => $request->input('bg_date_to'),
            'expiry_from'  => $request->input('expiry_from'),
            'expiry_to'    => $request->input('expiry_to'),
            'claim_from'   => $request->input('claim_from'),
            'claim_to'     => $request->input('claim_to'),
        ], fn($value) => $value !== null && $value !== '');
    }

    /**
     * Apply all filters to a BankGuarantee query.
     * Only sales orders flagged sd_pbg = 1 are ever included.
     */
    private function applyFilters($query, array $filters)
    {
        // Core rule: SD/PBG = Yes only
        $query->whereHas('salesOrder', fn($q) => $q->where('sd_pbg', 1));

        if (! empty($filters['client'])) {
            $query->whereHas('salesOrder.customer', fn($q) =>
            $q->where('customers.name', 'like', '%' . $filters['client'] . '%'));
        }

        if (! empty($filters['bg_number'])) {
            $query->where('bg_number', 'like', '%' . $filters['bg_number'] . '%');
        }

        if (! empty($filters['po_no'])) {
            $query->whereHas('salesOrder', fn($q) =>
            $q->where('customer_po_number', 'like', '%' . $filters['po_no'] . '%'));
        }

        if (! empty($filters['invoice_no'])) {
            $query->whereHas('invoice', fn($q) =>
            $q->where('invoice_number', 'like', '%' . $filters['invoice_no'] . '%'));
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['bg_date_from'])) {
            $query->whereDate('bg_date', '>=', $filters['bg_date_from']);
        }
        if (! empty($filters['bg_date_to'])) {
            $query->whereDate('bg_date', '<=', $filters['bg_date_to']);
        }
        if (! empty($filters['expiry_from'])) {
            $query->whereDate('expiry_date', '>=', $filters['expiry_from']);
        }
        if (! empty($filters['expiry_to'])) {
            $query->whereDate('expiry_date', '<=', $filters['expiry_to']);
        }
        if (! empty($filters['claim_from'])) {
            $query->whereDate('claim_date', '>=', $filters['claim_from']);
        }
        if (! empty($filters['claim_to'])) {
            $query->whereDate('claim_date', '<=', $filters['claim_to']);
        }

        return $query;
    }

    /**
     * SD/PBG Register list page.
     */
    public function index(Request $request)
    {
        $filters = $this->extractFilters($request);

        $guarantees = $this->applyFilters(BankGuarantee::query(), $filters)
            ->with(['salesOrder.customer', 'invoice'])
            ->orderBy('bg_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        $totalRecords = $guarantees->total();

        return view('admin.sd-pbg.index', [
            'guarantees' => $guarantees,
            'totalRecords' => $totalRecords,
            'filters' => $filters,
            'statuses' => BankGuarantee::STATUSES,
        ]);
    }

    /**
     * Export filtered SD/PBG (Yes) records to Excel.
     */
    public function exportExcel(Request $request, SdPbgExportService $exportService)
    {
        try {
            $filters = $this->extractFilters($request);

            $guarantees = $this->applyFilters(BankGuarantee::query(), $filters)
                ->with(['salesOrder.customer', 'invoice'])
                ->orderBy('bg_date', 'desc')
                ->get();

            if ($guarantees->isEmpty()) {
                return response()->json([
                    'message' => 'No SD/PBG records available for export.',
                ], 404);
            }

            $spreadsheet = $exportService->exportExcel($guarantees, $filters, $guarantees->count());

            Log::info('SD/PBG register exported', [
                'user_id' => auth()->id(),
                'records' => $guarantees->count(),
                'filters' => $filters,
            ]);

            return $exportService->downloadExcel($spreadsheet);
        } catch (\Throwable $e) {
            Log::error('SD/PBG export failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Export failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
