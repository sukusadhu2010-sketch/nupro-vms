@extends('layouts.app')

@section('title', 'Record Payment - ' . $invoice->invoice_number)

@section('content')
    <div class="container-fluid py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
                <li class="breadcrumb-item"><a
                        href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></li>
                <li class="breadcrumb-item active">Record Payment</li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            Record Payment for Invoice {{ $invoice->invoice_number }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('invoices.store-payment', $invoice) }}" id="paymentForm">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6 text-white">
                                    <label class="form-label fw-bold">Customer</label>
                                    <p class="mb-1 fw-semibold">{{ $invoice->customer->company }}</p>
                                    <small class="">{{ $invoice->customer->name }} -
                                        {{ $invoice->customer->phone }}</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Invoice Total</label>
                                    <p class="h5 text-success mb-0">₹{{ number_format($invoice->total_amount, 2) }}</p>
                                    <small class="text-white">Paid:
                                        ₹{{ number_format($invoice->paid_amount, 2) }}</small><br>
                                    <small class="text-danger fw-bold">Outstanding:
                                        ₹{{ number_format($invoice->outstanding_amount, 2) }}</small>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="amount"
                                        class="form-control @error('amount') is-invalid @enderror" step="0.01"
                                        min="0.01" max="{{ $invoice->outstanding_amount }}" required
                                        value="{{ old('amount') }}">
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Max allowed:
                                        ₹{{ number_format($invoice->outstanding_amount, 2) }}</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date"
                                        class="form-control @error('payment_date') is-invalid @enderror" required
                                        value="{{ old('payment_date', now()->toDateString()) }}">
                                    @error('payment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                                    <select name="payment_mode" id="paymentMode"
                                        class="form-select @error('payment_mode') is-invalid @enderror" required>
                                        <option value="">Select Mode</option>
                                        <option value="cash" {{ old('payment_mode') == 'cash' ? 'selected' : '' }}>Cash
                                        </option>
                                        <option value="fund_transfer"
                                            {{ old('payment_mode') == 'fund_transfer' ? 'selected' : '' }}>Fund Transfer
                                        </option>
                                        <option value="cheque" {{ old('payment_mode') == 'cheque' ? 'selected' : '' }}>
                                            Cheque</option>
                                        <option value="upi" {{ old('payment_mode') == 'upi' ? 'selected' : '' }}>UPI
                                        </option>
                                    </select>
                                    @error('payment_mode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Conditional Fields -->
                            <div id="conditionalFields" style="display: none;" class="mt-3">
                                <!-- UPI -->
                                <div id="upiFields" class="field-group" style="display: none;">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">UPI ID <span class="text-danger">*</span></label>
                                            <input type="text" name="upi_id"
                                                class="form-control @error('upi_id') is-invalid @enderror" maxlength="100"
                                                value="{{ old('upi_id') }}">
                                            @error('upi_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Cheque -->
                                <div id="chequeFields" class="field-group" style="display: none;">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Cheque Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="cheque_number"
                                                class="form-control @error('cheque_number') is-invalid @enderror"
                                                maxlength="50" value="{{ old('cheque_number') }}">
                                            @error('cheque_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Bank Details <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="bank_details"
                                                class="form-control @error('bank_details') is-invalid @enderror"
                                                maxlength="500" value="{{ old('bank_details') }}">
                                            @error('bank_details')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Fund Transfer -->
                                <div id="fundTransferFields" class="field-group" style="display: none;">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Beneficiary Details <span
                                                    class="text-danger">*</span></label>
                                            <textarea name="beneficiary_details" class="form-control @error('beneficiary_details') is-invalid @enderror"
                                                rows="2" maxlength="500">{{ old('beneficiary_details') }}</textarea>
                                            @error('beneficiary_details')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Transaction ID <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="transaction_id"
                                                class="form-control @error('transaction_id') is-invalid @enderror"
                                                maxlength="100" value="{{ old('transaction_id') }}">
                                            @error('transaction_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-12">
                                    <label class="form-label">Notes (Optional)</label>
                                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" maxlength="500">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-success btn-lg px-5">
                                    <i class="fas fa-check me-2"></i>
                                    Record Payment
                                </button>
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary btn-lg ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Invoice
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Payment History Sidebar -->
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-history me-2 text-success"></i>
                            Payment History
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @if ($invoice->payments->count() > 0)
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach ($invoice->payments as $payment)
                                            <tr>
                                                <td>
                                                    <div class="small fw-semibold text-success">
                                                        ₹{{ number_format($payment->amount, 2) }}</div>
                                                    <div class="small text-muted">
                                                        {{ $payment->payment_date->format('d M Y') }}</div>
                                                    <div
                                                        class="small badge bg-{{ strtolower($payment->mode_label) }} px-2 py-1">
                                                        {{ $payment->mode_label }}</div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-3 opacity-50"></i>
                                <p>No payments recorded yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modeSelect = document.getElementById('paymentMode');
            const conditionalFields = document.getElementById('conditionalFields');

            function toggleFields() {
                const mode = modeSelect.value;
                conditionalFields.style.display = mode ? 'block' : 'none';

                // Hide all groups
                document.querySelectorAll('.field-group').forEach(group => {
                    group.style.display = 'none';
                });

                // Show specific group
                if (mode === 'upi') {
                    document.getElementById('upiFields').style.display = 'block';
                } else if (mode === 'cheque') {
                    document.getElementById('chequeFields').style.display = 'block';
                } else if (mode === 'fund_transfer') {
                    document.getElementById('fundTransferFields').style.display = 'block';
                }
            }

            modeSelect.addEventListener('change', toggleFields);
            toggleFields(); // Initial call
        });
    </script>
@endsection
