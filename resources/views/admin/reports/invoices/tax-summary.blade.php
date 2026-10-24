@extends('layouts.app')

@section('title', 'GST Tax Summary Report')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-percent me-2 text-primary"></i>GST Tax Summary Report
            </h4>
            <p class="text-muted mb-0">GST rate-wise taxable values, tax split (CGST/SGST/IGST), and liability summary</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print Summary
        </button>
    </div>

    <!-- Date Range Filter -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.invoices.tax-summary') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-calendar-check me-1"></i>Generate Summary
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary KPI cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light p-3">
                <span class="small text-muted fw-semibold">Total CGST Output</span>
                <h5 class="fw-bold mb-0 text-primary">₹{{ number_format($totals['cgst'], 2) }}</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light p-3">
                <span class="small text-muted fw-semibold">Total SGST Output</span>
                <h5 class="fw-bold mb-0 text-info">₹{{ number_format($totals['sgst'], 2) }}</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light p-3">
                <span class="small text-muted fw-semibold">Total IGST Output</span>
                <h5 class="fw-bold mb-0 text-warning">₹{{ number_format($totals['igst'], 2) }}</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light p-3">
                <span class="small text-muted fw-semibold">Total Tax Liability</span>
                <h5 class="fw-bold mb-0 text-danger">₹{{ number_format($totals['cgst'] + $totals['sgst'] + $totals['igst'] + $totals['cess'], 2) }}</h5>
            </div>
        </div>
    </div>

    <!-- Rate-wise Breakup Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Tax Slab Rate Breakup</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>GST Slab Rate</th>
                            <th class="text-center">Invoices Count</th>
                            <th class="text-end">Taxable Value (₹)</th>
                            <th class="text-end">Calculated Tax (₹)</th>
                            <th class="text-end">Effective Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($taxBreakup as $slab)
                            <tr>
                                <td class="fw-bold">
                                    <span class="badge bg-primary fs-6">{{ $slab->tax_percent }}%</span>
                                </td>
                                <td class="text-center">{{ $slab->invoice_count }}</td>
                                <td class="text-end font-monospace">₹{{ number_format($slab->total_taxable, 2) }}</td>
                                <td class="text-end font-monospace text-danger">₹{{ number_format($slab->total_tax, 2) }}</td>
                                <td class="text-end font-monospace fw-bold">₹{{ number_format($slab->total_taxable + $slab->total_tax, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No transaction data available for the chosen date range.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

