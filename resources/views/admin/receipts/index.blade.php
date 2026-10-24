@extends('layouts.app')

@section('title', 'Customer Receipts')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-hand-holding-usd me-2 text-success"></i>Customer Receipts
            </h4>
            <p class="text-muted mb-0">Record incoming payments from customers and allocate against invoices</p>
        </div>
        @if (auth()->user()->canAccess('receipts', 'create'))
            <a href="{{ route('receipts.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>New Customer Receipt
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
            <div class="card shadow-sm border-0 border-start border-success border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Total Collections</span>
                    <h4 class="fw-bold mb-0 text-success mt-1">₹{{ number_format($metrics['total_received'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-info border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">This Month's Inflow</span>
                    <h4 class="fw-bold mb-0 text-info mt-1">₹{{ number_format($metrics['this_month'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-danger border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Total Unpaid Invoices</span>
                    <h4 class="fw-bold mb-0 text-danger mt-1">₹{{ number_format($metrics['total_outstanding'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('receipts.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Receipt #, Ref #, Customer..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Customer</label>
                    <select name="customer_id" class="form-select form-select-sm select2">
                        <option value="">All Customers</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Payment Mode</label>
                    <select name="payment_mode" class="form-select form-select-sm">
                        <option value="">All Modes</option>
                        <option value="cash" {{ request('payment_mode') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ request('payment_mode') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cheque" {{ request('payment_mode') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="neft" {{ request('payment_mode') == 'neft' ? 'selected' : '' }}>NEFT</option>
                        <option value="rtgs" {{ request('payment_mode') == 'rtgs' ? 'selected' : '' }}>RTGS</option>
                        <option value="upi" {{ request('payment_mode') == 'upi' ? 'selected' : '' }}>UPI</option>
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
                    <a href="{{ route('receipts.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="fas fa-redo"></i></a>
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
                            <th class="text-end">Amount Received</th>
                            <th>Allocated Invoices</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($receipts as $rcpt)
                            <tr>
                                <td>
                                    <a href="{{ route('receipts.show', $rcpt) }}" class="fw-bold text-decoration-none">
                                        {{ $rcpt->payment_number }}
                                    </a>
                                </td>
                                <td class="small">{{ $rcpt->payment_date->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $rcpt->customer?->name ?? '—' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $rcpt->mode_label }}
                                    </span>
                                </td>
                                <td class="small">
                                    {{ $rcpt->reference_number ?: '—' }}
                                    @if ($rcpt->bank_name)
                                        <div class="text-muted small">{{ $rcpt->bank_name }}</div>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-success font-monospace">
                                    ₹{{ number_format($rcpt->amount, 2) }}
                                </td>
                                <td class="small">
                                    @forelse ($rcpt->allocations as $alloc)
                                        <span class="badge bg-light text-primary border me-1">
                                            {{ $alloc->invoice?->invoice_number }} (₹{{ number_format($alloc->allocated_amount, 2) }})
                                        </span>
                                    @empty
                                        <span class="text-muted">Unallocated Advance</span>
                                    @endforelse
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('receipts.show', $rcpt) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if (auth()->user()->canAccess('receipts', 'print'))
                                            <a href="{{ route('receipts.print', $rcpt) }}" target="_blank" class="btn btn-outline-secondary" title="Print Voucher">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        @endif
                                        @if (auth()->user()->canAccess('receipts', 'delete'))
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteReceipt({{ $rcpt->id }})" title="Reverse / Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-receipt fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                    No receipts recorded matching your criteria.
                                </td>
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

@push('scripts')
<script>
function deleteReceipt(id) {
    if (!confirm('Are you sure you want to delete this receipt? This will REVERSE the paid amounts on the allocated invoices.')) return;
    fetch(`{{ url('receipts') }}/${id}`, {
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
            alert(res.message || 'Error deleting receipt');
        }
    })
    .catch(() => alert('Network error occurred'));
}
</script>
@endpush
@endsection
