@extends('layouts.app')

@section('title', 'Invoice Outstanding Report')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-exclamation-circle me-2 text-danger"></i>Invoice Outstanding & Aging Report
            </h4>
            <p class="text-muted mb-0">Unpaid and partially paid invoices categorized by aging brackets</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print Report
        </button>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.invoices.outstanding') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Customer</label>
                    <select name="customer_id" class="form-select form-select-sm select2">
                        <option value="">All Customers</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                </div>
                <div class="col-md-5 text-end">
                    <span class="small text-muted d-block">Total Overdue Receivables:</span>
                    <h4 class="fw-bold text-danger mb-0">₹{{ number_format($totalOutstanding, 2) }}</h4>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th class="text-end">Invoice Amount</th>
                            <th class="text-end">Paid Amount</th>
                            <th class="text-end">Outstanding Due</th>
                            <th>Days Aging</th>
                            <th>Aging Bracket</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $inv)
                            <tr>
                                <td class="fw-semibold">
                                    <a href="{{ route('invoices.show', $inv) }}" class="text-decoration-none">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td class="small">{{ $inv->invoice_date->format('d/m/Y') }}</td>
                                <td>{{ $inv->customer?->name }}</td>
                                <td class="text-end font-monospace">₹{{ number_format($inv->grand_total, 2) }}</td>
                                <td class="text-end font-monospace text-success">₹{{ number_format($inv->paid_amount, 2) }}</td>
                                <td class="text-end font-monospace fw-bold text-danger">₹{{ number_format($inv->outstanding, 2) }}</td>
                                <td>{{ $inv->aging_days }} days</td>
                                <td>
                                    @php
                                        $badge = match($inv->aging_bucket) {
                                            '0-30 days' => 'bg-success',
                                            '31-60 days' => 'bg-info text-dark',
                                            '61-90 days' => 'bg-warning text-dark',
                                            default => 'bg-danger',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $inv->aging_bucket }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('receipts.create', ['customer_id' => $inv->customer_id, 'invoice_id' => $inv->id]) }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-receipt me-1"></i>Receive
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-check-circle fa-3x mb-3 text-success d-block"></i>
                                    Excellent! No outstanding customer balances found.
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

