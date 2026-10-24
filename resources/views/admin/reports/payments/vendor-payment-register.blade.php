@extends('layouts.app')

@section('title', 'Vendor Payment Register')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-file-invoice-dollar me-2 text-danger"></i>Vendor Payment Register
            </h4>
            <p class="text-muted mb-0">Disbursement logs, purchase order links, and vendor payment tracking</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print Register
        </button>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.payments.vendor-payment-register') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Vendor</label>
                    <select name="vendor_id" class="form-select form-select-sm select2">
                        <option value="">All Vendors</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
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
                    <a href="{{ route('reports.payments.vendor-payment-register') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-redo"></i></a>
                </div>
                <div class="col-md-2 text-end">
                    <span class="small text-muted d-block">Total Disbursed:</span>
                    <h5 class="fw-bold text-danger mb-0">₹{{ number_format($totalAmount, 2) }}</h5>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Vendor</th>
                            <th>Linked PO</th>
                            <th>Mode</th>
                            <th>Reference / Bank</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $p)
                            <tr>
                                <td class="fw-semibold font-monospace">
                                    <a href="{{ route('vendor-payments.show', $p) }}" class="text-decoration-none">
                                        {{ $p->payment_number }}
                                    </a>
                                </td>
                                <td>{{ $p->payment_date->format('d/m/Y') }}</td>
                                <td>{{ $p->vendor?->name }}</td>
                                <td>
                                    @if ($p->purchaseOrder)
                                        <a href="{{ route('procurements.show', $p->purchaseOrder) }}" class="small">
                                            {{ $p->purchaseOrder->po_number }}
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $p->mode_label }}</span></td>
                                <td class="small">{{ $p->reference_number ?: '—' }}</td>
                                <td class="text-end font-monospace fw-bold text-danger">₹{{ number_format($p->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No vendor payments found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($payments->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

