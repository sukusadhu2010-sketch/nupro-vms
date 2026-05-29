@extends('layouts.app')

@section('title', 'Edit Invoice')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
                <h1 class="h2 mb-1">
                    <i class="fas fa-edit me-2 text-warning"></i>Edit Invoice #{{ $invoice->invoice_number }}
                </h1>
                <p class="text-muted">Update invoice details</p>
            </div>
        </div>

        <form method="POST" action="{{ route('invoices.update', $invoice) }}">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <!-- Invoice Details -->
                <div class="col-lg-8">
                    <div class="card shadow-lg">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Invoice Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Invoice Number</label>
                                    <input type="text" class="form-control" value="{{ $invoice->invoice_number }}"
                                        disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Invoice Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="invoice_date" class="form-control"
                                        value="{{ old('invoice_date', $invoice->invoice_date->toDateString()) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Due Date</label>
                                    <input type="date" name="due_date" class="form-control"
                                        value="{{ old('due_date', $invoice->due_date?->toDateString()) }}">
                                </div>
                            </div>

                            <!-- Customer Info -->
                            <div class="p-3 bg-light rounded mb-4">
                                <h6 class="fw-bold mb-2">Customer Information</h6>
                                <p class="mb-0">{{ $invoice->customer->company }} - {{ $invoice->customer->name }}</p>
                                <p class="mb-0 text-muted">{{ $invoice->customer->email }} |
                                    {{ $invoice->customer->phone }}</p>
                            </div>

                            <!-- Invoice Items (Read Only) -->
                            <div>
                                <label class="form-label fw-bold mb-3">Invoice Items</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Description</th>
                                                <th class="text-end">Quantity</th>
                                                <th class="text-end">Unit Price</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($invoice->items as $index => $item)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $item->description }}</td>
                                                    <td class="text-end">{{ $item->quantity }}</td>
                                                    <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                                    <td class="text-end">₹{{ number_format($item->subtotal, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="4" class="text-end">Subtotal:</th>
                                                <th class="text-end">₹{{ number_format($invoice->subtotal, 2) }}</th>
                                            </tr>
                                            <tr>
                                                <th colspan="4" class="text-end">Tax:</th>
                                                <th class="text-end">₹{{ number_format($invoice->tax_amount, 2) }}</th>
                                            </tr>
                                            <tr class="table-success">
                                                <th colspan="4" class="text-end h5">Total:</th>
                                                <th class="text-end h5">₹{{ number_format($invoice->total_amount, 2) }}
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mt-4">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes...">{{ old('notes', $invoice->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Status:</span>
                                <span class="badge bg-{{ $invoice->status_badge }}">{{ $invoice->status_label }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Amount:</span>
                                <span class="text-success fw-bold">₹{{ number_format($invoice->total_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Paid Amount:</span>
                                <span>₹{{ number_format($invoice->paid_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Outstanding:</span>
                                <span
                                    class="{{ $invoice->outstanding_amount > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                    ₹{{ number_format($invoice->outstanding_amount, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-warning btn-lg w-100">
                            <i class="fas fa-save me-2"></i>Update Invoice
                        </button>
                        <a href="{{ route('invoices.show', $invoice) }}"
                            class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
