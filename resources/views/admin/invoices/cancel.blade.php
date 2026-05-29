@extends('layouts.app')

@section('title', 'Cancel Invoice')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-lg">
                    <div class="card-header bg-danger bg-opacity-10">
                        <h5 class="mb-0 fw-bold text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Cancel Invoice
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-warning me-2"></i>
                            <strong>Warning:</strong> This action cannot be undone. Once cancelled, the invoice will be
                            marked as cancelled and cannot be activated again.
                        </div>

                        <div class="mb-4">
                            <strong>Invoice:</strong> {{ $invoice->invoice_number }}<br>
                            <strong>Customer:</strong> {{ $invoice->customer->company }}<br>
                            <strong>Amount:</strong> ₹{{ number_format($invoice->total_amount, 2) }}<br>
                            <strong>Status:</strong>
                            <span class="badge bg-{{ $invoice->status_badge }}">{{ $invoice->status_label }}</span>
                        </div>

                        <form action="{{ route('invoices.cancel', $invoice) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="mb-3">
                                <label class="form-label fw-bold">Reason for Cancellation <span
                                        class="text-danger">*</span></label>
                                <textarea name="cancellation_reason" class="form-control" rows="4"
                                    placeholder="Please provide a detailed reason for cancelling this invoice..." required minlength="10"></textarea>
                                @error('cancellation_reason')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-times-circle me-2"></i>Cancel Invoice
                                </button>
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
