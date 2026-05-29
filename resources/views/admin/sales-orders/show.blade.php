@extends('layouts.app')

@section('title', 'Sales Order ' . $salesOrder->order_number)

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-shopping-cart me-2 text-success"></i>
                            Sales Order {{ $salesOrder->order_number }}
                        </h4>
                        <p class="text-muted mb-0">View sales order details</p>
                    </div>
                    <div>
                        <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        @if ($salesOrder->status !== 'delivered')
                            <a href="{{ route('sales-orders.edit', $salesOrder) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-2"></i>Edit
                            </a>
                        @endif
                        @if (in_array($salesOrder->status, ['confirmed', 'processing', 'delivered']) && !$salesOrder->has_active_invoice)
                            <form action="{{ route('sales-orders.generate-invoice', $salesOrder) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Generate an invoice from this sales order?');">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-file-invoice me-2"></i>Generate Invoice
                                </button>
                            </form>
                        @endif
                        @if (in_array($salesOrder->status, ['confirmed', 'processing']))
                            <form action="{{ route('sales-orders.procure', $salesOrder) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Generate vendor-wise procurement orders from this sales order?');">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-truck me-2"></i>Generate Procurement
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Session Alerts -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Order Info & Customer Cards -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light fw-bold">Order Information</div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr style="display: none">
                                        <th>Order Number</th>
                                        <td>{{ $salesOrder->order_number }}</td>
                                    </tr>
                                    <tr>
                                        <th>Linked Quotation</th>
                                        <td>
                                            @if ($salesOrder->quotation)
                                                <a href="{{ route('quotations.show', $salesOrder->quotation) }}"
                                                    class="badge bg-primary text-decoration-none">
                                                    {{ $salesOrder->quotation->formatted_quote_number }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>PO Number</th>
                                        <td>{{ $salesOrder->po_number ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Job Number</th>
                                        <td>{{ $salesOrder->job_number ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge {{ $salesOrder->status_badge }}">
                                                {{ $salesOrder->status_label }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Total Amount</th>
                                        <td class="text-success fw-bold">
                                            ₹{{ number_format($salesOrder->total_amount, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Expected Delivery</th>
                                        <td>{{ $salesOrder->expected_delivery_date?->format('Y-m-d') ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Created</th>
                                        <td>{{ $salesOrder->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light fw-bold">Customer &amp; Shipping</div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <th>Customer</th>
                                        <td>{{ $salesOrder->customer->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $salesOrder->customer->email ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone</th>
                                        <td>{{ $salesOrder->customer->phone ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Shipping Address</th>
                                        <td>{{ $salesOrder->shipping_address ?? '—' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items Table -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light fw-bold">Order Items</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($salesOrder->items as $item)
                                        <tr>
                                            <td>{{ $item->product->name ?? 'N/A' }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                            <td class="fw-bold">₹{{ number_format($item->subtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
