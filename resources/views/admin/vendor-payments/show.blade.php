@extends('layouts.app')

@section('title', 'Vendor Payment - ' . $vendorPayment->payment_number)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="mb-0 fw-bold">
                    <i class="fas fa-money-check-alt me-2 text-danger"></i>Vendor Payment #{{ $vendorPayment->payment_number }}
                </h4>
                <span class="badge bg-success px-3 py-1 rounded-pill">Completed</span>
            </div>
            <p class="text-muted mb-0">Disbursed on {{ $vendorPayment->payment_date->format('d M Y') }} &bull; Recorded by {{ $vendorPayment->createdBy?->name ?? 'System' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vendor-payments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
            @if (auth()->user()->canAccess('vendor-payments', 'print'))
                <a href="{{ route('vendor-payments.print', $vendorPayment) }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="fas fa-print me-1"></i>Print Voucher
                </a>
            @endif
            @if (auth()->user()->canAccess('vendor-payments', 'delete'))
                <button type="button" class="btn btn-outline-danger" onclick="deleteVendorPayment({{ $vendorPayment->id }})">
                    <i class="fas fa-trash me-1"></i>Delete
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
        <!-- Vendor Info -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-store me-2 text-primary"></i>Vendor / Payee Details
                    </h6>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold mb-1">{{ $vendorPayment->vendor?->name ?? '—' }}</h5>
                    <div class="small text-muted mb-3">{{ $vendorPayment->vendor?->address }}</div>
                    <div class="row g-2 small">
                        <div class="col-6"><strong>GSTIN:</strong> {{ $vendorPayment->vendor?->gst_no ?? '—' }}</div>
                        <div class="col-6"><strong>Contact Person:</strong> {{ $vendorPayment->vendor?->contact_person ?? '—' }}</div>
                        <div class="col-6"><strong>Phone:</strong> {{ $vendorPayment->vendor?->phone ?? '—' }}</div>
                        <div class="col-6"><strong>Email:</strong> {{ $vendorPayment->vendor?->email ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Meta -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle me-2 text-danger"></i>Disbursement Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="small text-muted d-block">Amount Disbursed:</span>
                            <h4 class="fw-bold text-danger mb-0">₹{{ number_format($vendorPayment->amount, 2) }}</h4>
                        </div>
                        <div class="col-6">
                            <span class="small text-muted d-block">Payment Date:</span>
                            <span class="fw-semibold">{{ $vendorPayment->payment_date->format('d M Y') }}</span>
                        </div>
                        <div class="col-6">
                            <span class="small text-muted d-block">Payment Mode:</span>
                            <span class="badge bg-light text-dark border fs-6">{{ $vendorPayment->mode_label }}</span>
                        </div>
                        <div class="col-6">
                            <span class="small text-muted d-block">Linked PO:</span>
                            @if ($vendorPayment->purchaseOrder)
                                <a href="{{ route('procurements.show', $vendorPayment->purchaseOrder) }}" class="fw-semibold text-decoration-none">
                                    {{ $vendorPayment->purchaseOrder->po_number }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                        <div class="col-6">
                            <span class="small text-muted d-block">Reference / UTR / Cheque:</span>
                            <span class="fw-semibold font-monospace">{{ $vendorPayment->reference_number ?: '—' }}</span>
                        </div>
                        @if ($vendorPayment->bank_name)
                            <div class="col-6">
                                <span class="small text-muted d-block">Bank Name:</span>
                                <span>{{ $vendorPayment->bank_name }}</span>
                            </div>
                        @endif
                        @if ($vendorPayment->remarks)
                            <div class="col-12">
                                <span class="small text-muted d-block">Remarks:</span>
                                <p class="small mb-0 text-dark">{{ $vendorPayment->remarks }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteVendorPayment(id) {
    if (!confirm('Are you sure you want to delete this payment record?')) return;
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
            window.location.href = "{{ route('vendor-payments.index') }}";
        } else {
            alert(res.message || 'Error deleting payment');
        }
    })
    .catch(() => alert('Network error occurred'));
}
</script>
@endpush
@endsection
