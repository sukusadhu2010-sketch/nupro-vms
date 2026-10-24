@extends('layouts.app')

@section('title', 'Record Vendor Payment')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-money-check-alt me-2 text-danger"></i>Record Vendor Payment
            </h4>
            <p class="text-muted mb-0">Record outgoing payment to vendor/supplier against purchase order</p>
        </div>
        <a href="{{ route('vendor-payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Payments
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('vendor-payments.store') }}" method="POST" id="vendorPaymentForm">
        @csrf

        <div class="row g-4 mb-4">
            <!-- Payment Details -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-file-invoice-dollar me-2 text-danger"></i>Payment Particulars
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Number <span class="text-danger">*</span></label>
                                <input type="text" name="payment_number" class="form-control font-monospace fw-bold"
                                    value="{{ old('payment_number', $paymentNumber) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control"
                                    value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Vendor / Supplier <span class="text-danger">*</span></label>
                                <select name="vendor_id" id="vendor_id" class="form-select select2" required>
                                    <option value="">-- Choose Vendor --</option>
                                    @foreach ($vendors as $v)
                                        <option value="{{ $v->id }}" {{ old('vendor_id', $selectedVendorId) == $v->id ? 'selected' : '' }}>
                                            {{ $v->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Linked Purchase Order (PO)</label>
                                <select name="purchase_order_id" id="purchase_order_id" class="form-select select2">
                                    <option value="">-- Direct Payment / No Specific PO --</option>
                                    @foreach ($procurements as $po)
                                        <option value="{{ $po->id }}"
                                            data-vendor="{{ $po->vendor_id }}"
                                            {{ old('purchase_order_id', $selectedPoId) == $po->id ? 'selected' : '' }}>
                                            {{ $po->po_number }} &bull; {{ $po->vendor?->name }} (₹{{ number_format($po->total_amount, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control fw-bold fs-5 text-danger"
                                        value="{{ old('amount') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                                <select name="payment_mode" class="form-select" required>
                                    <option value="bank_transfer" {{ old('payment_mode') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="neft" {{ old('payment_mode') == 'neft' ? 'selected' : '' }}>NEFT</option>
                                    <option value="rtgs" {{ old('payment_mode') == 'rtgs' ? 'selected' : '' }}>RTGS</option>
                                    <option value="cheque" {{ old('payment_mode') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="upi" {{ old('payment_mode') == 'upi' ? 'selected' : '' }}>UPI</option>
                                    <option value="cash" {{ old('payment_mode') == 'cash' ? 'selected' : '' }}>Cash</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instrument Reference -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-university me-2 text-primary"></i>Bank / Reference Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Reference / UTR / Cheque #</label>
                                <input type="text" name="reference_number" class="form-control"
                                    value="{{ old('reference_number') }}" placeholder="e.g. UTR88771199">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control"
                                    value="{{ old('bank_name') }}" placeholder="e.g. State Bank of India">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cheque / Settlement Date</label>
                                <input type="date" name="cheque_date" class="form-control"
                                    value="{{ old('cheque_date') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Remarks / Payment Notes</label>
                                <textarea name="remarks" rows="3" class="form-control"
                                    placeholder="Enter any notes regarding this vendor disbursement...">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('vendor-payments.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-danger btn-lg px-4">
                <i class="fas fa-check-circle me-2"></i>Save Vendor Payment
            </button>
        </div>
    </form>
</div>
@endsection

