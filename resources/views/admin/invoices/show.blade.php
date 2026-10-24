@extends('layouts.app')

@section('title', 'Invoice - ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid py-4">
    <!-- Header with Action Buttons -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="mb-0 fw-bold">
                    <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Invoice #{{ $invoice->invoice_number }}
                </h4>
                <span class="badge {{ $invoice->status_badge }} px-3 py-1 rounded-pill">
                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                </span>
                <span class="badge bg-light text-dark border">
                    {{ $invoice->type_label }}
                </span>
            </div>
            <p class="text-muted mb-0">Created on {{ $invoice->created_at->format('d M Y, h:i A') }} by {{ $invoice->createdBy?->name ?? 'System' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
            @if (auth()->user()->canAccess('invoices', 'print'))
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="fas fa-print me-1"></i>Print A4 / PDF
                </a>
            @endif
            @if (auth()->user()->canAccess('receipts', 'create') && $invoice->outstanding > 0 && in_array($invoice->status, ['approved', 'partially_paid']))
                <a href="{{ route('receipts.create', ['customer_id' => $invoice->customer_id, 'invoice_id' => $invoice->id]) }}" class="btn btn-success">
                    <i class="fas fa-hand-holding-usd me-1"></i>Receive Payment
                </a>
            @endif
            @if (auth()->user()->canAccess('invoices', 'approve') && in_array($invoice->status, ['draft', 'pending']))
                <form action="{{ route('invoices.approve', $invoice) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Approve this invoice? This will confirm the tax document.')">
                        <i class="fas fa-check-circle me-1"></i>Approve Invoice
                    </button>
                </form>
            @endif
            @if (auth()->user()->canAccess('invoices', 'edit') && in_array($invoice->status, ['draft', 'pending']))
                <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
            @endif
            @if (auth()->user()->canAccess('invoices', 'delete') && $invoice->status !== 'cancelled' && $invoice->paid_amount == 0)
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="fas fa-ban me-1"></i>Cancel Invoice
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
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Customer & Billing Info -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-tie me-2 text-primary"></i>Customer & Billed To
                    </h6>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold text-dark mb-1">{{ $invoice->customer?->name }}</h5>
                    @if ($invoice->customer_gst)
                        <div class="text-muted small mb-3"><strong>GSTIN:</strong> {{ $invoice->customer_gst }}</div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-muted fw-semibold">Billing Address:</label>
                            <p class="small mb-0 text-dark" style="white-space: pre-line;">{{ $invoice->billing_address ?: 'Same as registered address' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted fw-semibold">Shipping Address:</label>
                            <p class="small mb-0 text-dark" style="white-space: pre-line;">{{ $invoice->shipping_address ?: 'Same as billing address' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Meta & References -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-file-contract me-2 text-primary"></i>Document References
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="small text-muted d-block">Invoice Date:</span>
                            <span class="fw-semibold">{{ $invoice->invoice_date->format('d M Y') }}</span>
                        </div>
                        <div class="col-6">
                            <span class="small text-muted d-block">Sales Order:</span>
                            @if ($invoice->salesOrder)
                                <a href="{{ route('sales-orders.show', $invoice->salesOrder) }}" class="fw-semibold text-decoration-none">
                                    {{ $invoice->salesOrder->order_number }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                        <div class="col-6">
                            <span class="small text-muted d-block">Customer PO Number:</span>
                            <span class="fw-semibold">{{ $invoice->po_number ?: '—' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="small text-muted d-block">Customer PO Date:</span>
                            <span class="fw-semibold">{{ $invoice->po_date ? $invoice->po_date->format('d M Y') : '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-list me-2 text-primary"></i>Item Details
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Item & Description</th>
                            <th>HSN/SAC</th>
                            <th class="text-end">Qty</th>
                            <th>Unit</th>
                            <th class="text-end">Rate (₹)</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">GST %</th>
                            <th class="text-end">Tax Amt (₹)</th>
                            <th class="text-end">Total Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item->item_name }}</div>
                                    @if ($item->description)
                                        <div class="small text-muted">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item->hsn_code ?: '—' }}</span></td>
                                <td class="text-end fw-semibold">{{ number_format($item->quantity, 3) }}</td>
                                <td>{{ $item->unit }}</td>
                                <td class="text-end">₹{{ number_format($item->rate, 2) }}</td>
                                <td class="text-end text-danger">
                                    @if ($item->discount_amount > 0)
                                        -₹{{ number_format($item->discount_amount, 2) }}
                                        <small class="d-block text-muted">({{ $item->discount_percent }}%)</small>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">{{ $item->tax_percent }}%</td>
                                <td class="text-end">₹{{ number_format($item->tax_amount, 2) }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light">
            <div class="row justify-content-end">
                <div class="col-md-5">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="fw-semibold">Sub Total:</td>
                            <td class="text-end">₹{{ number_format($invoice->sub_total, 2) }}</td>
                        </tr>
                        @if ($invoice->discount_amount > 0)
                            <tr>
                                <td class="fw-semibold text-danger">Discount:</td>
                                <td class="text-end text-danger">-₹{{ number_format($invoice->discount_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="fw-semibold">Taxable Amount:</td>
                            <td class="text-end fw-bold">₹{{ number_format($invoice->taxable_amount, 2) }}</td>
                        </tr>
                        @if ($invoice->cgst > 0)
                            <tr>
                                <td>CGST:</td>
                                <td class="text-end">₹{{ number_format($invoice->cgst, 2) }}</td>
                            </tr>
                        @endif
                        @if ($invoice->sgst > 0)
                            <tr>
                                <td>SGST:</td>
                                <td class="text-end">₹{{ number_format($invoice->sgst, 2) }}</td>
                            </tr>
                        @endif
                        @if ($invoice->igst > 0)
                            <tr>
                                <td>IGST:</td>
                                <td class="text-end">₹{{ number_format($invoice->igst, 2) }}</td>
                            </tr>
                        @endif
                        @if ($invoice->cess > 0)
                            <tr>
                                <td>CESS:</td>
                                <td class="text-end">₹{{ number_format($invoice->cess, 2) }}</td>
                            </tr>
                        @endif
                        @if ($invoice->tcs > 0)
                            <tr>
                                <td>TCS:</td>
                                <td class="text-end">₹{{ number_format($invoice->tcs, 2) }}</td>
                            </tr>
                        @endif
                        @if ($invoice->shipping_charge > 0)
                            <tr>
                                <td>Shipping:</td>
                                <td class="text-end">₹{{ number_format($invoice->shipping_charge, 2) }}</td>
                            </tr>
                        @endif
                        @if ($invoice->round_off != 0)
                            <tr>
                                <td>Round Off:</td>
                                <td class="text-end">₹{{ number_format($invoice->round_off, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="border-top border-dark">
                            <th class="fs-5">Grand Total:</th>
                            <th class="text-end fs-5 text-primary">₹{{ number_format($invoice->grand_total, 2) }}</th>
                        </tr>
                        <tr>
                            <td class="fw-bold text-success">Paid Amount:</td>
                            <td class="text-end fw-bold text-success">₹{{ number_format($invoice->paid_amount, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <th class="fs-6 {{ $invoice->outstanding > 0 ? 'text-danger' : 'text-muted' }}">Balance Due:</th>
                            <th class="text-end fs-6 {{ $invoice->outstanding > 0 ? 'text-danger' : 'text-muted' }}">
                                ₹{{ number_format($invoice->outstanding, 2) }}
                            </th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Received against this Invoice -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-receipt me-2 text-success"></i>Customer Payment Allocations
            </h6>
            @if (auth()->user()->canAccess('receipts', 'create') && $invoice->outstanding > 0)
                <a href="{{ route('receipts.create', ['customer_id' => $invoice->customer_id, 'invoice_id' => $invoice->id]) }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-plus me-1"></i>Add Receipt
                </a>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Payment Mode</th>
                            <th>Reference / Cheque</th>
                            <th class="text-end">Allocated Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoice->allocations as $alloc)
                            <tr>
                                <td>
                                    @if ($alloc->payment)
                                        <a href="{{ route('receipts.show', $alloc->payment) }}" class="fw-bold text-decoration-none">
                                            {{ $alloc->payment->payment_number }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $alloc->payment?->payment_date?->format('d M Y') ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $alloc->payment?->mode_label ?? '—' }}
                                    </span>
                                </td>
                                <td>{{ $alloc->payment?->reference_number ?? '—' }}</td>
                                <td class="text-end fw-bold text-success">₹{{ number_format($alloc->allocated_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">
                                    No payments received against this invoice yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Invoice Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('invoices.cancel', $invoice) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">Cancel Invoice #{{ $invoice->invoice_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Are you sure you want to cancel this invoice? Once cancelled, it cannot be edited or reopened.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Specify why this invoice is being cancelled..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

