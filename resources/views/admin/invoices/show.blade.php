@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
                        <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h2 mb-1">
                            <i class="fas fa-file-invoice text-info me-2"></i>
                            Invoice {{ $invoice->invoice_number }}
                        </h1>
                        <p class="text-white mb-0">
                            @if ($invoice->salesOrder)
                                Related to Sales Order:
                                <a href="{{ route('sales-orders.show', $invoice->salesOrder) }}"
                                    class="text-decoration-none">
                                    {{ $invoice->salesOrder->order_number }}
                                </a>
                            @endif
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-{{ $invoice->status_badge }} px-4 py-2 rounded-pill fs-6">
                            {{ $invoice->status_label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Invoice Details -->
            <div class="col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-info-circle me-2 text-info"></i>
                                Invoice Details
                            </h5>
                            <div class="d-flex gap-2">
                                <a href="{{ route('invoices.print', $invoice) }}" target="_blank"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-print me-1"></i>Print
                                </a>
                                @if ($invoice->status === 'draft')
                                    <form action="{{ route('invoices.sent', $invoice) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-paper-plane me-1"></i>Mark as Sent
                                        </button>
                                    </form>
                                @endif
                                @if ($invoice->status !== 'cancelled' && $invoice->status !== 'paid')
                                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body text-white">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3 text-primary">Customer</h6>
                                <p class="mb-1 fw-semibold">{{ $invoice->customer->company }}</p>
                                <p class="mb-0 text-white">{{ $invoice->customer->name }}</p>
                                <p class="mb-0 text-white">{{ $invoice->customer->email }}</p>
                                <p class="mb-0 text-white">{{ $invoice->customer->phone }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3 text-primary">Invoice Info</h6>
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td class="text-white" style="width: 120px;">Invoice Date:</td>
                                        <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-white">Due Date:</td>
                                        <td>
                                            @if ($invoice->due_date)
                                                <span
                                                    class="{{ $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'text-danger fw-bold' : '' }}">
                                                    {{ $invoice->due_date->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="text-white">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-white">Created By:</td>
                                        <td>{{ $invoice->creator->name ?? 'System' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-white">Created At:</td>
                                        <td>{{ $invoice->created_at->format('d M Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="mt-4">
                            <h6 class="fw-bold mb-3 text-primary">Invoice Items</h6>
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
                                                <td>
                                                    <div class="fw-semibold">{{ $item->description }}</div>
                                                </td>
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
                                            <th colspan="4" class="text-end h5">Total Amount:</th>
                                            <th class="text-end text-success h5">
                                                ₹{{ number_format($invoice->total_amount, 2) }}</th>
                                        </tr>
                                        <tr class="table-info">
                                            <th colspan="4" class="text-end">Paid Amount:</th>
                                            <th class="text-end">₹{{ number_format($invoice->paid_amount, 2) }}</th>
                                        </tr>
                                        <tr
                                            class="{{ $invoice->outstanding_amount > 0 ? 'table-warning' : 'table-success' }}">
                                            <th colspan="4" class="text-end">Outstanding:</th>
                                            <th
                                                class="text-end {{ $invoice->outstanding_amount > 0 ? 'text-danger' : 'text-success' }}">
                                                ₹{{ number_format($invoice->outstanding_amount, 2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Notes -->
                        @if ($invoice->notes)
                            <div class="mt-4">
                                <h6 class="fw-bold mb-2 text-primary">Notes</h6>
                                <p class="mb-0 text-white">{{ $invoice->notes }}</p>
                            </div>
                        @endif

                        <!-- Cancellation Info -->
                        @if ($invoice->is_cancelled)
                            <div class="mt-4 p-3 bg-danger bg-opacity-10 rounded border border-danger">
                                <h6 class="fw-bold text-danger">Invoice Cancelled</h6>
                                <p class="mb-1">Reason: {{ $invoice->cancellation_reason }}</p>
                                <p class="mb-0">Cancelled by: {{ $invoice->canceller->name ?? 'Unknown' }} on
                                    {{ $invoice->cancelled_at->format('d M Y H:i') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Summary & Actions -->
            <div class="col-lg-4">
                <!-- Payment Section -->
                @if (in_array($invoice->status, ['sent', 'partial']) && !$invoice->is_cancelled)
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Record Payment</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('invoices.payment', $invoice) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Amount</label>
                                    <input type="number" name="amount" class="form-control" step="0.01"
                                        max="{{ $invoice->outstanding_amount }}" required>
                                    <small class="text-white">Outstanding:
                                        ₹{{ number_format($invoice->outstanding_amount, 2) }}
                                    </small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check me-2"></i>Record Payment
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Detailed Payment Entry -->
                @if (in_array($invoice->status, ['sent', 'partial']) && !$invoice->is_cancelled)
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-primary bg-opacity-25">
                            <h6 class="mb-0 fw-bold text-primary">Record Detailed Payment</h6>
                        </div>
                        <div class="card-body py-3">
                            <a href="{{ route('invoices.payment-entry', $invoice) }}" class="btn btn-primary w-100">
                                <i class="fas fa-money-bill-wave me-2"></i>Detailed Payment Form
                            </a>
                            <div class="text-center mt-2">
                                <small class="text-muted">Full form with UPI, Cheque, Bank Transfer options</small>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Payment History -->
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-success bg-opacity-10 border-success">
                        <h6 class="mb-0 fw-bold text-success">
                            <i class="fas fa-history me-2"></i>
                            Payment History ({{ $invoice->payments->count() }})
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @if ($invoice->payments->count() > 0)
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach ($invoice->payments as $payment)
                                            <tr>
                                                <td class="py-2">
                                                    <div class="h6 mb-1 text-success">
                                                        ₹{{ number_format($payment->amount, 2) }}</div>
                                                    <div class="small text-muted">
                                                        {{ $payment->payment_date->format('d M Y H:i') }}</div>
                                                    <div class="mt-1">
                                                        <span
                                                            class="badge bg-{{ $payment->payment_mode === 'cash' ? 'secondary' : ($payment->payment_mode === 'upi' ? 'info' : ($payment->payment_mode === 'cheque' ? 'warning' : 'primary')) }} px-3 py-2">
                                                            {{ $payment->mode_label }}
                                                        </span>
                                                    </div>
                                                    @if ($payment->details)
                                                        <div class="small text-muted mt-2">{{ $payment->details }}</div>
                                                    @endif
                                                    @if ($payment->notes)
                                                        <small
                                                            class="d-block text-muted mt-1">{{ $payment->notes }}</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-50 text-success"></i>
                                <h6 class="text-muted">No payments recorded</h6>
                                <p class="mb-0">Payments will appear here once recorded</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Cancel Section -->


                @if (!$invoice->is_cancelled && $invoice->status !== 'paid')
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-danger bg-opacity-10">
                            <h6 class="mb-0 fw-bold text-danger">Cancel Invoice</h6>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('invoices.cancel-form', $invoice) }}" class="btn btn-outline-danger w-100">
                                <i class="fas fa-times-circle me-2"></i>Cancel Invoice
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Audit Trail -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold">
                            <i class="fas fa-history me-2 text-info"></i>
                            Audit Trail
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    @forelse ($invoice->auditLogs as $log)
                                        <tr>
                                            <td class="py-2">
                                                <div class="small fw-semibold">{{ $log->action_label }}</div>
                                                <div class=" small">
                                                    {{ $log->user->name ?? 'System' }} -
                                                    {{ $log->created_at->format('d M Y H:i') }}
                                                </div>
                                                @if ($log->notes)
                                                    <div class="small mt-1">{{ $log->notes }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center py-3 text-white">No audit logs yet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="mt-4">
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left me-2"></i>Back to Invoices
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
