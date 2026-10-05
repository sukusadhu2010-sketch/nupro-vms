@extends('layouts.app')

@section('title', 'Create Sales Order')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-plus me-2 text-success"></i>
                            Create Sales Order
                        </h4>
                        <p class="text-muted mb-0">Convert a quotation into a sales order</p>
                    </div>
                    <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body">
                        <form action="{{ route('sales-orders.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="quotation_id" class="form-label fw-bold">Select Quotation</label>
                                <select name="quotation_id" id="quotation_id" class="form-select" required>
                                    <option value="">— Choose a quotation —</option>
                                    @foreach ($quotations as $quotation)
                                        <option value="{{ $quotation->id }}"
                                            {{ old('quotation_id') == $quotation->id ? 'selected' : '' }}>
                                            {{ $quotation->quote_number }} —
                                            {{ $quotation->enquiry->customer->name ?? 'N/A' }} ({{ $quotation->status }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('quotation_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="job_number_preview" class="form-label fw-bold">Job Number</label>
                                    <input type="text" id="job_number_preview" class="form-control bg-light"
                                        value="Auto-generated ({{ $nextJobNumber }})" readonly disabled>
                                    <div class="form-text">A unique Job Number is generated automatically when the order is created.</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="customer_po_number" class="form-label fw-bold">Customer PO Number</label>
                                    <input type="text" name="customer_po_number" id="customer_po_number" class="form-control"
                                        value="{{ old('customer_po_number') }}" placeholder="Enter customer PO number">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="customer_po_date" class="form-label fw-bold">Customer PO Date</label>
                                    <input type="date" name="customer_po_date" id="customer_po_date" class="form-control"
                                        value="{{ old('customer_po_date') }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold d-block">MTC</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="mtc" id="mtc_yes" value="1"
                                            {{ old('mtc') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="mtc_yes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="mtc" id="mtc_no" value="0"
                                            {{ old('mtc') !== '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="mtc_no">No</label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold d-block">PDI</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="pdi" id="pdi_yes" value="1"
                                            {{ old('pdi') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pdi_yes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="pdi" id="pdi_no" value="0"
                                            {{ old('pdi') !== '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pdi_no">No</label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold d-block">SD/PBG</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="sd_pbg" id="sd_pbg_yes" value="1"
                                            {{ old('sd_pbg') == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="sd_pbg_yes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="sd_pbg" id="sd_pbg_no" value="0"
                                            {{ old('sd_pbg') !== '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="sd_pbg_no">No</label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="delivery_target_date" class="form-label fw-bold">Delivery Target Date</label>
                                    <input type="date" name="delivery_target_date" id="delivery_target_date" class="form-control"
                                        value="{{ old('delivery_target_date') }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="payment_mode" class="form-label fw-bold">Payment Mode</label>
                                    <select name="payment_mode" id="payment_mode" class="form-select">
                                        <option value="">— Select —</option>
                                        @foreach (\App\Models\SalesOrder::PAYMENT_MODE_LABELS as $value => $label)
                                            <option value="{{ $value }}" {{ old('payment_mode') === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('payment_mode')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row" id="credit-days-row" style="display: {{ in_array(old('payment_mode'), ['lc', 'credit']) ? 'flex' : 'none' }};">
                                <div class="col-md-3 mb-3">
                                    <label for="credit_days" class="form-label fw-bold">No. of Days</label>
                                    <input type="number" name="credit_days" id="credit_days" class="form-control" min="1" max="365"
                                        value="{{ old('credit_days') }}" placeholder="e.g. 30">
                                    <div class="form-text">Required for LC and Credit payment modes.</div>
                                    @error('credit_days')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-2"></i>Convert to Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const paymentMode = document.getElementById('payment_mode');
                const creditRow = document.getElementById('credit-days-row');

                function toggleCreditDays() {
                    creditRow.style.display = ['lc', 'credit'].includes(paymentMode.value) ? 'flex' : 'none';
                }

                paymentMode.addEventListener('change', toggleCreditDays);
                toggleCreditDays();
            });
        </script>
    @endpush
@endsection
