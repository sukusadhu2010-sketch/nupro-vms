@extends('layouts.app')

@section('title', 'Invoice Register Report')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-book me-2 text-primary"></i>Invoice Register Report
            </h4>
            <p class="text-muted mb-0">Chronological listing of invoices with taxable values, GST breakups, and totals</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print Register
        </button>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.invoices.register') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Customer</label>
                    <select name="customer_id" class="form-select form-select-sm select2">
                        <option value="">All Customers</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="tax" {{ request('type') == 'tax' ? 'selected' : '' }}>Tax Invoice</option>
                        <option value="proforma" {{ request('type') == 'proforma' ? 'selected' : '' }}>Proforma</option>
                        <option value="commercial" {{ request('type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter"></i></button>
                    <a href="{{ route('reports.invoices.register') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light p-3">
                <span class="small text-muted fw-semibold">Total Taxable Value</span>
                <h5 class="fw-bold mb-0 text-dark">₹{{ number_format($totals['taxable'], 2) }}</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light p-3">
                <span class="small text-muted fw-semibold">Total GST (CGST+SGST+IGST)</span>
                <h5 class="fw-bold mb-0 text-dark">₹{{ number_format($totals['cgst'] + $totals['sgst'] + $totals['igst'], 2) }}</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light p-3">
                <span class="small text-muted fw-semibold">Total Invoice Amount</span>
                <h5 class="fw-bold mb-0 text-primary">₹{{ number_format($totals['grand_total'], 2) }}</h5>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-light p-3">
                <span class="small text-muted fw-semibold">Total Realized (Paid)</span>
                <h5 class="fw-bold mb-0 text-success">₹{{ number_format($totals['paid_amount'], 2) }}</h5>
            </div>
        </div>
    </div>

    <!-- Register Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>GSTIN</th>
                            <th class="text-end">Taxable (₹)</th>
                            <th class="text-end">CGST (₹)</th>
                            <th class="text-end">SGST (₹)</th>
                            <th class="text-end">IGST (₹)</th>
                            <th class="text-end">Total (₹)</th>
                            <th class="text-end">Paid (₹)</th>
                            <th>Status</th>
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
                                <td class="small text-muted font-monospace">{{ $inv->customer_gst ?: '—' }}</td>
                                <td class="text-end font-monospace">₹{{ number_format($inv->taxable_amount, 2) }}</td>
                                <td class="text-end font-monospace">₹{{ number_format($inv->cgst, 2) }}</td>
                                <td class="text-end font-monospace">₹{{ number_format($inv->sgst, 2) }}</td>
                                <td class="text-end font-monospace">₹{{ number_format($inv->igst, 2) }}</td>
                                <td class="text-end font-monospace fw-bold">₹{{ number_format($inv->grand_total, 2) }}</td>
                                <td class="text-end font-monospace text-success">₹{{ number_format($inv->paid_amount, 2) }}</td>
                                <td>
                                    <span class="badge {{ $inv->status_badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $inv->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">
                                    No invoices found in this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($invoices->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

