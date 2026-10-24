@extends('layouts.app')

@section('title', 'Accounts Aging Analysis')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-hourglass-half me-2 text-primary"></i>Accounts Receivables Aging Analysis
            </h4>
            <p class="text-muted mb-0">Customer aging analysis across standard 30-day collection intervals</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print Aging Report
        </button>
    </div>

    <!-- Aging Interval Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card shadow-sm border-0 border-start border-success border-4 p-3">
                <span class="small text-muted fw-semibold">0 - 30 Days</span>
                <h5 class="fw-bold mb-0 text-success mt-1">₹{{ number_format($agingSummary['0_30'], 2) }}</h5>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm border-0 border-start border-info border-4 p-3">
                <span class="small text-muted fw-semibold">31 - 60 Days</span>
                <h5 class="fw-bold mb-0 text-info mt-1">₹{{ number_format($agingSummary['31_60'], 2) }}</h5>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm border-0 border-start border-warning border-4 p-3">
                <span class="small text-muted fw-semibold">61 - 90 Days</span>
                <h5 class="fw-bold mb-0 text-warning mt-1">₹{{ number_format($agingSummary['61_90'], 2) }}</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-danger border-4 p-3">
                <span class="small text-muted fw-semibold">> 90 Days (Critical)</span>
                <h5 class="fw-bold mb-0 text-danger mt-1">₹{{ number_format($agingSummary['90_plus'], 2) }}</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-dark border-4 p-3 bg-light">
                <span class="small text-muted fw-semibold">Total Outstanding</span>
                <h5 class="fw-bold mb-0 text-dark mt-1">₹{{ number_format($agingSummary['total'], 2) }}</h5>
            </div>
        </div>
    </div>

    <!-- Aging Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Customer-wise Aging Breakdown</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Customer Name</th>
                            <th class="text-end">0 - 30 Days (₹)</th>
                            <th class="text-end">31 - 60 Days (₹)</th>
                            <th class="text-end">61 - 90 Days (₹)</th>
                            <th class="text-end">90+ Days (₹)</th>
                            <th class="text-end">Total Due (₹)</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customerAging as $custId => $row)
                            <tr>
                                <td class="fw-bold">{{ $row['customer'] }}</td>
                                <td class="text-end font-monospace">{{ $row['0_30'] > 0 ? '₹' . number_format($row['0_30'], 2) : '—' }}</td>
                                <td class="text-end font-monospace {{ $row['31_60'] > 0 ? 'text-info fw-semibold' : '' }}">
                                    {{ $row['31_60'] > 0 ? '₹' . number_format($row['31_60'], 2) : '—' }}
                                </td>
                                <td class="text-end font-monospace {{ $row['61_90'] > 0 ? 'text-warning fw-semibold' : '' }}">
                                    {{ $row['61_90'] > 0 ? '₹' . number_format($row['61_90'], 2) : '—' }}
                                </td>
                                <td class="text-end font-monospace {{ $row['90_plus'] > 0 ? 'text-danger fw-bold' : '' }}">
                                    {{ $row['90_plus'] > 0 ? '₹' . number_format($row['90_plus'], 2) : '—' }}
                                </td>
                                <td class="text-end font-monospace fw-bold fs-6 text-primary">
                                    ₹{{ number_format($row['total'], 2) }}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('reports.invoices.customer-ledger', ['customer_id' => $custId]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-balance-scale me-1"></i>Ledger
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success d-block"></i>
                                    All customer accounts are clear with zero outstanding balances.
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

