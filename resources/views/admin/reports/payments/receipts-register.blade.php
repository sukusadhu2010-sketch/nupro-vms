@extends('layouts.app')

@section('title', 'Customer Receipts Register')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-file-invoice me-2 text-success"></i>Customer Receipts Register
            </h4>
            <p class="text-muted mb-0">Complete historical record of incoming payments, payment instruments, and settlement links</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print Register
        </button>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.payments.receipts-register') }}" class="row g-3 align-items-end">
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
                    <label class="form-label small fw-semibold">Payment Mode</label>
                    <select name="payment_mode" class="form-select form-select-sm">
                        <option value="">All Modes</option>
                        <option value="bank_transfer" {{ request('payment_mode') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="neft" {{ request('payment_mode') == 'neft' ? 'selected' : '' }}>NEFT</option>
                        <option value="rtgs" {{ request('payment_mode') == 'rtgs' ? 'selected' : '' }}>RTGS</option>
                        <option value="cheque" {{ request('payment_mode') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="upi" {{ request('payment_mode') == 'upi' ? 'selected' : '' }}>UPI</option>
                        <option value="cash" {{ request('payment_mode') == 'cash' ? 'selected' : '' }}>Cash</option>
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
                    <a href="{{ route('reports.payments.receipts-register') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-redo"></i></a>
                </div>
                <div class="col-md-2 text-end">
                    <span class="small text-muted d-block">Total Collections:</span>
                    <h5 class="fw-bold text-success mb-0">₹{{ number_format($totalAmount, 2) }}</h5>
                </div>
            </form>
        </div>
    </div>

    <!-- Receipts Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Mode</th>
                            <th>Reference / Bank</th>
                            <th class="text-end">Amount (₹)</th>
                            <th>Settled Invoices</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($receipts as $r)
                            <tr>
                                <td class="fw-semibold font-monospace">
                                    <a href="{{ route('receipts.show', $r) }}" class="text-decoration-none">
                                        {{ $r->payment_number }}
                                    </a>
                                </td>
                                <td>{{ $r->payment_date->format('d/m/Y') }}</td>
                                <td>{{ $r->customer?->name }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $r->mode_label }}</span></td>
                                <td class="small">{{ $r->reference_number ?: '—' }}</td>
                                <td class="text-end font-monospace fw-bold text-success">₹{{ number_format($r->amount, 2) }}</td>
                                <td class="small">
                                    @forelse ($r->allocations as $a)
                                        <span class="badge bg-light text-primary border me-1">{{ $a->invoice?->invoice_number }}</span>
                                    @empty
                                        <span class="text-muted">Advance</span>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No receipts found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($receipts->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $receipts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

