@extends('layouts.app')

@section('title', 'Accounts & Financial Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-chart-line me-2 text-primary"></i>Accounts & Financial Dashboard
            </h4>
            <p class="text-muted mb-0">Overview of receivables, payables, cash flow, and recent settlement activities</p>
        </div>
        <div class="d-flex gap-2">
            @if (auth()->user()->canAccess('receipts', 'create'))
                <a href="{{ route('receipts.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i>New Receipt
                </a>
            @endif
            @if (auth()->user()->canAccess('vendor-payments', 'create'))
                <a href="{{ route('vendor-payments.create') }}" class="btn btn-danger btn-sm">
                    <i class="fas fa-plus me-1"></i>New Vendor Payment
                </a>
            @endif
        </div>
    </div>

    <!-- Main 4 Financial KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Total Receivables</span>
                    <h3 class="fw-bold mb-1 text-primary mt-1">₹{{ number_format($totalReceivable, 2) }}</h3>
                    <small class="text-muted">Uncollected customer invoices</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-danger border-4 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Overdue Receivables (>30 Days)</span>
                    <h3 class="fw-bold mb-1 text-danger mt-1">₹{{ number_format($overdueReceivables, 2) }}</h3>
                    <small class="text-muted">Requires customer follow-up</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Total Payables</span>
                    <h3 class="fw-bold mb-1 text-warning mt-1">₹{{ number_format($totalPayable, 2) }}</h3>
                    <small class="text-muted">Supplier & vendor commitments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-dark border-4 h-100">
                <div class="card-body">
                    <span class="text-muted small fw-semibold text-uppercase">Overdue Payables (>30 Days)</span>
                    <h3 class="fw-bold mb-1 text-dark mt-1">₹{{ number_format($overduePayables, 2) }}</h3>
                    <small class="text-muted">Awaiting disbursement</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 6-Month Cash Flow Bar Summary -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-coins me-2 text-warning"></i>6-Month Cash Flow Overview (Collections vs Disbursements)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start">Month</th>
                            @foreach ($months as $m)
                                <th>{{ $m['month'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start fw-bold text-success">Collections (Inflow)</td>
                            @foreach ($months as $m)
                                <td class="font-monospace text-success fw-bold">₹{{ number_format($m['inflow'], 2) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="text-start fw-bold text-danger">Disbursements (Outflow)</td>
                            @foreach ($months as $m)
                                <td class="font-monospace text-danger fw-bold">₹{{ number_format($m['outflow'], 2) }}</td>
                            @endforeach
                        </tr>
                        <tr class="table-light">
                            <td class="text-start fw-bold">Net Cash Flow</td>
                            @foreach ($months as $m)
                                @php $net = $m['inflow'] - $m['outflow']; @endphp
                                <td class="font-monospace fw-bold {{ $net >= 0 ? 'text-primary' : 'text-danger' }}">
                                    ₹{{ number_format($net, 2) }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Split Grids -->
    <div class="row g-4">
        <!-- Recent Receipts -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-success">
                        <i class="fas fa-arrow-circle-down me-2"></i>Recent Customer Receipts
                    </h6>
                    <a href="{{ route('receipts.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentReceipts as $r)
                                    <tr>
                                        <td>
                                            <a href="{{ route('receipts.show', $r) }}" class="fw-semibold font-monospace text-decoration-none">
                                                {{ $r->payment_number }}
                                            </a>
                                        </td>
                                        <td class="small">{{ $r->payment_date->format('d/m/Y') }}</td>
                                        <td class="small">{{ $r->customer?->name }}</td>
                                        <td class="text-end fw-bold text-success font-monospace">₹{{ number_format($r->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">No recent receipts</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Vendor Payments -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-danger">
                        <i class="fas fa-arrow-circle-up me-2"></i>Recent Vendor Payments
                    </h6>
                    <a href="{{ route('vendor-payments.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Payment #</th>
                                    <th>Date</th>
                                    <th>Vendor</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentPayments as $p)
                                    <tr>
                                        <td>
                                            <a href="{{ route('vendor-payments.show', $p) }}" class="fw-semibold font-monospace text-decoration-none">
                                                {{ $p->payment_number }}
                                            </a>
                                        </td>
                                        <td class="small">{{ $p->payment_date->format('d/m/Y') }}</td>
                                        <td class="small">{{ $p->vendor?->name }}</td>
                                        <td class="text-end fw-bold text-danger font-monospace">₹{{ number_format($p->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">No recent vendor payments</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

