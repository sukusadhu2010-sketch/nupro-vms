@extends('layouts.app')

@section('title', 'Vendor Payments')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-money-check-alt me-2 text-danger"></i>Vendor Payments
            </h4>
            <p class="text-muted mb-0">Record outgoing payments to suppliers and vendors against purchase orders</p>
        </div>
        @if (auth()->user()->canAccess('vendor-payments', 'create'))
            <a href="{{ route('vendor-payments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Record Vendor Payment
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-danger border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Total Paid to Vendors</span>
                    <h4 class="fw-bold mb-0 text-danger mt-1">₹{{ number_format($metrics['total_paid'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-warning border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">This Month's Outflow</span>
                    <h4 class="fw-bold mb-0 text-warning mt-1">₹{{ number_format($metrics['this_month'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Total PO Commitments</span>
                    <h4 class="fw-bold mb-0 text-primary mt-1">₹{{ number_format($metrics['total_po_value'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('vendor-payments.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Payment #, Ref #, Vendor..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Vendor</label>
                    <select name="vendor_id" class="form-select form-select-sm select2">
                        <option value="">All Vendors</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->name }}
                            </option>
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
                <div class="col-md-1">
                    <label class="form-label small fw-semibold">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100" title="Filter"><i class="fas fa-filter"></i></button>
                    <a href="{{ route('vendor-payments.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="fas fa-redo"></i></a>
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
                            <th>Payment Mode</th>
                            <th>Reference / Bank</th>
                            <th class="text-end">Amount Paid</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $pymt)
                            <tr>
                                <td>
                                    <a href="{{ route('vendor-payments.show', $pymt) }}" class="fw-bold text-decoration-none">
                                        {{ $pymt->payment_number }}
                                    </a>
                                </td>
                                <td class="small">{{ $pymt->payment_date->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $pymt->vendor?->name ?? '—' }}</div>
                                </td>
                                <td>
                                    @if ($pymt->purchaseOrder)
                                        <a href="{{ route('procurements.show', $pymt->purchaseOrder) }}" class="small text-decoration-none">
                                            {{ $pymt->purchaseOrder->po_number }}
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $pymt->mode_label }}
                                    </span>
                                </td>
                                <td class="small">
                                    {{ $pymt->reference_number ?: '—' }}
                                    @if ($pymt->bank_name)
                                        <div class="text-muted small">{{ $pymt->bank_name }}</div>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-danger font-monospace">
                                    ₹{{ number_format($pymt->amount, 2) }}
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('vendor-payments.show', $pymt) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if (auth()->user()->canAccess('vendor-payments', 'print'))
                                            <a href="{{ route('vendor-payments.print', $pymt) }}" target="_blank" class="btn btn-outline-secondary" title="Print Voucher">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        @endif
                                        @if (auth()->user()->canAccess('vendor-payments', 'delete'))
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteVendorPayment({{ $pymt->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-money-check-alt fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                    No vendor payments found matching your criteria.
                                </td>
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

@push('scripts')
<script>
function deleteVendorPayment(id) {
    if (!confirm('Are you sure you want to delete this vendor payment record?')) return;
    fetch(`{{ url('vendor-payments') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status) {
            window.location.reload();
        } else {
            alert(res.message || 'Error deleting payment');
        }
    })
    .catch(() => alert('Network error occurred'));
}
</script>
@endpush
@endsection
