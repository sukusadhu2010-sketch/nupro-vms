@extends('layouts.app')

@section('title', 'Receivable & Payable Summary')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-balance-scale-left me-2 text-primary"></i>Receivable & Payable Summary Report
            </h4>
            <p class="text-muted mb-0">High-level working capital analysis comparing total claims against obligations</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print Summary
        </button>
    </div>

    <!-- Summary Balance Sheet Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Total Receivables (Current Assets)</span>
                    <h3 class="fw-bold mb-1 text-success mt-2">₹{{ number_format($totalReceivable, 2) }}</h3>
                    <small class="text-muted">Total unpaid customer invoices</small>
                    <div class="mt-3 pt-2 border-top text-muted small">
                        Total Historical Realized: <strong>₹{{ number_format($totalCollected, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-danger border-4 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Total Payables (Current Liabilities)</span>
                    <h3 class="fw-bold mb-1 text-danger mt-2">₹{{ number_format($totalPayable, 2) }}</h3>
                    <small class="text-muted">Outstanding vendor PO commitments</small>
                    <div class="mt-3 pt-2 border-top text-muted small">
                        Total Historical Disbursed: <strong>₹{{ number_format($totalVendorPaid, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-{{ $netWorkingCapital >= 0 ? 'primary' : 'warning' }} border-4 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Net Working Capital Position</span>
                    <h3 class="fw-bold mb-1 text-{{ $netWorkingCapital >= 0 ? 'primary' : 'warning' }} mt-2">
                        ₹{{ number_format($netWorkingCapital, 2) }}
                    </h3>
                    <small class="text-muted">Receivables minus Payables</small>
                    <div class="mt-3 pt-2 border-top text-muted small">
                        Solvency Ratio: <strong>{{ $totalPayable > 0 ? number_format($totalReceivable / $totalPayable, 2) . 'x' : 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breakdown Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Liquidity Summary Statement</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Financial Component</th>
                        <th>Classification</th>
                        <th class="text-end">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-semibold">1. Customer Receivables (Unpaid Invoices)</td>
                        <td><span class="badge bg-success">Asset</span></td>
                        <td class="text-end font-monospace fw-bold text-success">₹{{ number_format($totalReceivable, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">2. Supplier Payables (Pending PO Disbursements)</td>
                        <td><span class="badge bg-danger">Liability</span></td>
                        <td class="text-end font-monospace fw-bold text-danger">₹{{ number_format($totalPayable, 2) }}</td>
                    </tr>
                    <tr class="table-light fs-6 fw-bold">
                        <td>Net Working Capital (1 - 2)</td>
                        <td>Status: {{ $netWorkingCapital >= 0 ? 'Surplus' : 'Deficit' }}</td>
                        <td class="text-end font-monospace text-primary">₹{{ number_format($netWorkingCapital, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

