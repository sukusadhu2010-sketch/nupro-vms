@extends('layouts.app')

@section('title', 'Customer Receipt - ' . $receipt->payment_number)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="mb-0 fw-bold">
                    <i class="fas fa-receipt me-2 text-success"></i>Receipt #{{ $receipt->payment_number }}
                </h4>
                <span class="badge bg-success px-3 py-1 rounded-pill">Completed</span>
            </div>
            <p class="text-muted mb-0">Recorded on {{ $receipt->created_at->format('d M Y, h:i A') }} by {{ $receipt->createdBy?->name ?? 'System' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
            @if (auth()->user()->canAccess('receipts', 'print'))
                <a href="{{ route('receipts.print', $receipt) }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="fas fa-print me-1"></i>Print Receipt Voucher
                </a>
            @endif
            @if (auth()->user()->canAccess('receipts', 'delete'))
                <button type="button" class="btn btn-outline-danger" onclick="deleteReceipt({{ $receipt->id }})">
                    <i class="fas fa-trash me-1"></i>Reverse / Delete
                </button>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Customer & Payment Details -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-tie me-2 text-primary"></i>Customer Information
                    </h6>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold mb-1">{{ $receipt->customer?->name ?? '—' }}</h5>
                    <div class="small text-muted mb-3">{{ $receipt->customer?->address }}</div>
                    <div class="row g-2 small">
                        <div class="col-6"><strong>GSTIN:</strong> {{ $receipt->customer?->gstin ?? 'URP' }}</div>
                        <div class="col-6"><strong>Phone:</strong> {{ $receipt->customer?->phone ?? '—' }}</div>
                        <div class="col-12"><strong>Email:</strong> {{ $receipt->customer?->email ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Payment Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="small text-muted d-block">Amount Received:</span>
                            <h4 class="fw-bold text-success mb-0">₹{{ number_format($receipt->amount, 2) }}</h4>
                        </div>
                        <div class="col-6">
                            <span class="small text-muted d-block">Payment Date:</span>
                            <span class="fw-semibold">{{ $receipt->payment_date->format('d M Y') }}</span>
                        </div>
                        <div class="col-6">
                            <span class="small text-muted d-block">Payment Mode:</span>
                            <span class="badge bg-light text-dark border fs-6">{{ $receipt->mode_label }}</span>
                        </div>
                        <div class="col-6">
                            <span class="small text-muted d-block">Reference / UTR / Cheque:</span>
                            <span class="fw-semibold font-monospace">{{ $receipt->reference_number ?: '—' }}</span>
                        </div>
                        @if ($receipt->bank_name)
                            <div class="col-6">
                                <span class="small text-muted d-block">Drawee Bank:</span>
                                <span>{{ $receipt->bank_name }}</span>
                            </div>
                        @endif
                        @if ($receipt->cheque_date)
                            <div class="col-6">
                                <span class="small text-muted d-block">Cheque Date:</span>
                                <span>{{ $receipt->cheque_date->format('d M Y') }}</span>
                            </div>
                        @endif
                        @if ($receipt->remarks)
                            <div class="col-12">
                                <span class="small text-muted d-block">Remarks:</span>
                                <p class="small mb-0 text-dark">{{ $receipt->remarks }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Allocations Grid -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-link me-2 text-primary"></i>Invoice Allocations
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice Number</th>
                            <th>Invoice Date</th>
                            <th class="text-end">Invoice Grand Total</th>
                            <th class="text-end">Amount Allocated</th>
                            <th>Current Invoice Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($receipt->allocations as $alloc)
                            <tr>
                                <td class="fw-semibold font-monospace">
                                    {{ $alloc->invoice?->invoice_number }}
                                </td>
                                <td>{{ $alloc->invoice?->invoice_date->format('d M Y') }}</td>
                                <td class="text-end font-monospace">₹{{ number_format($alloc->invoice?->grand_total, 2) }}</td>
                                <td class="text-end font-monospace fw-bold text-success">₹{{ number_format($alloc->allocated_amount, 2) }}</td>
                                <td>
                                    <span class="badge {{ $alloc->invoice?->status_badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $alloc->invoice?->status)) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if ($alloc->invoice)
                                        <a href="{{ route('invoices.show', $alloc->invoice) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No invoices allocated. This payment is recorded as an advance balance.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteReceipt(id) {
    if (!confirm('Are you sure you want to delete this receipt? This will reverse payments on the invoices.')) return;
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
            window.location.href = "{{ route('receipts.index') }}";
        } else {
            alert(res.message || 'Error deleting receipt');
        }
    })
    .catch(() => alert('Network error occurred'));
}
</script>
@endpush
@endsection
