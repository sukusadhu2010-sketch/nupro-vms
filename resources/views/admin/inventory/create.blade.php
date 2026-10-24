@extends('layouts.app')

@section('title', 'Record Inventory Movement')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-boxes me-2 text-primary"></i>Record Stock Transaction
            </h4>
            <p class="text-muted mb-0">Record Goods Receipt (GRN), Material Issue, Stock Transfer, or Physical Stock Adjustment</p>
        </div>
        <a href="{{ route('inventory-ledger.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Ledger
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

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <form action="{{ route('inventory-ledger.store') }}" method="POST">
                @csrf

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-dolly me-2 text-primary"></i>Movement Entry
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Transaction Date <span class="text-danger">*</span></label>
                                <input type="date" name="transaction_date" class="form-control"
                                    value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Transaction Type <span class="text-danger">*</span></label>
                                <select name="transaction_type" id="transaction_type" class="form-select" required>
                                    <option value="grn" {{ old('transaction_type', request('transaction_type', 'grn')) == 'grn' ? 'selected' : '' }}>GRN (Stock In / Received)</option>
                                    <option value="issue" {{ old('transaction_type', request('transaction_type')) == 'issue' ? 'selected' : '' }}>Issue Entry (Stock Out / Consumed)</option>
                                    <option value="transfer" {{ old('transaction_type', request('transaction_type')) == 'transfer' ? 'selected' : '' }}>Stock Transfer</option>
                                    <option value="adjustment" {{ old('transaction_type', request('transaction_type')) == 'adjustment' ? 'selected' : '' }}>Stock Adjustment (Correction)</option>
                                    <option value="po" {{ old('transaction_type', request('transaction_type')) == 'po' ? 'selected' : '' }}>Purchase Order Placement</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Reference Number</label>
                                <input type="text" name="reference_no" class="form-control"
                                    value="{{ old('reference_no') }}" placeholder="e.g. GRN/2026/001 or ISSUE-99">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Product / Item <span class="text-danger">*</span></label>
                                <select name="product_id" id="product_id" class="form-select select2" required>
                                    <option value="">-- Choose Product --</option>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}"
                                            data-unit="{{ $p->unit?->short_name ?? 'Nos' }}"
                                            {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->sku ? "[{$p->sku}] " : '' }}{{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" min="0.001" name="quantity" class="form-control fw-bold"
                                    value="{{ old('quantity') }}" required placeholder="0.000">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Unit</label>
                                <input type="text" name="unit" id="unit" class="form-control"
                                    value="{{ old('unit', 'Nos') }}" placeholder="Nos, Kgs, Mtr...">
                            </div>

                            <!-- Adjustment Direction (only visible if Adjustment chosen) -->
                            <div class="col-md-6 d-none" id="adjustmentDirectionBox">
                                <label class="form-label fw-semibold">Adjustment Type</label>
                                <select name="adjustment_direction" class="form-select">
                                    <option value="add">Add to Stock (+)</option>
                                    <option value="subtract">Deduct from Stock (-)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Linked Purchase Order (PO)</label>
                                <select name="purchase_order_id" id="purchase_order_id" class="form-select select2">
                                    <option value="">-- No Specific PO --</option>
                                    @foreach ($purchaseOrders as $po)
                                        <option value="{{ $po->id }}" data-vendor="{{ $po->vendor_id }}"
                                            {{ old('purchase_order_id', request('purchase_order_id')) == $po->id ? 'selected' : '' }}>
                                            {{ $po->po_number }} &bull; {{ $po->vendor?->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vendor / Supplier</label>
                                <select name="vendor_id" id="vendor_id" class="form-select select2">
                                    <option value="">-- No Vendor --</option>
                                    @foreach ($vendors as $v)
                                        <option value="{{ $v->id }}" {{ old('vendor_id', request('vendor_id')) == $v->id ? 'selected' : '' }}>
                                            {{ $v->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Remarks / Purpose</label>
                                <textarea name="remarks" rows="2" class="form-control"
                                    placeholder="Purpose of issue, GRN inspection note, transfer destination, etc.">{{ old('remarks') }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="allow_negative" id="allow_negative" value="1">
                                    <label class="form-check-label text-muted small" for="allow_negative">
                                        Explicitly allow negative inventory if stock is currently zero (Override validation)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light d-flex justify-content-end gap-2">
                        <a href="{{ route('inventory-ledger.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i>Post Transaction to Ledger
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const txType = document.getElementById('transaction_type');
    const adjBox = document.getElementById('adjustmentDirectionBox');
    const prodSelect = document.getElementById('product_id');
    const unitInput = document.getElementById('unit');
    const poSelect = document.getElementById('purchase_order_id');

    txType.addEventListener('change', function() {
        if (this.value === 'adjustment') {
            adjBox.classList.remove('d-none');
        } else {
            adjBox.classList.add('d-none');
        }
    });

    prodSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.getAttribute('data-unit')) {
            unitInput.value = opt.getAttribute('data-unit');
        }
    });

    poSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const vId = opt?.getAttribute('data-vendor');
        if (vId) {
            $('#vendor_id').val(vId).trigger('change');
        }
    });
});
</script>
@endpush
@endsection
