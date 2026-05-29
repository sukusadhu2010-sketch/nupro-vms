@extends('layouts.app')

@section('title', 'Procurement ' . $procurement->po_number)

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-truck me-2 text-danger"></i>
                            Procurement {{ $procurement->po_number }}
                        </h4>
                        <p class="text-muted mb-0">View purchase order details</p>
                    </div>
                    <div>
                        <a href="{{ route('procurements.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        @if ($procurement->status !== 'received')
                            <a href="{{ route('procurements.edit', $procurement) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-2"></i>Edit
                            </a>
                        @endif
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light fw-bold">P.O. Information</div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <th>P.O. Number</th>
                                        <td>{{ $procurement->po_number }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td><span
                                                class="badge {{ $procurement->status_badge }}">{{ $procurement->status_label }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Total Amount</th>
                                        <td class="text-success fw-bold">₹{{ number_format($procurement->total_amount, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Order Date</th>
                                        <td>{{ $procurement->order_date?->format('Y-m-d') ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Expected Delivery</th>
                                        <td>{{ $procurement->expected_delivery_date?->format('Y-m-d') ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Actual Delivery</th>
                                        <td>{{ $procurement->actual_delivery_date?->format('Y-m-d') ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Created</th>
                                        <td>{{ $procurement->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light fw-bold">Vendor</div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <th>Name</th>
                                        <td>{{ $procurement->vendor->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $procurement->vendor->email ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone</th>
                                        <td>{{ $procurement->vendor->phone ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Company</th>
                                        <td>{{ $procurement->vendor->company ?? '—' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light fw-bold">Linked Sales Order(s)</div>
                            <div class="card-body">
                                @if ($procurement->salesOrders->count() > 0)
                                    @foreach ($procurement->salesOrders as $so)
                                        <table class="table table-borderless mb-2">
                                            <tr>
                                                <th>Order #</th>
                                                <td><a
                                                        href="{{ route('sales-orders.show', $so) }}">{{ $so->order_number }}</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Customer</th>
                                                <td>{{ $so->customer->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td><span
                                                        class="badge {{ $so->status_badge }}">{{ $so->status_label }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                        @if (!$loop->last)
                                            <hr>
                                        @endif
                                    @endforeach
                                @elseif($procurement->salesOrder)
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <th>Order #</th>
                                            <td><a
                                                    href="{{ route('sales-orders.show', $procurement->salesOrder) }}">{{ $procurement->salesOrder->order_number }}</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Customer</th>
                                            <td>{{ $procurement->salesOrder->customer->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td><span
                                                    class="badge {{ $procurement->salesOrder->status_badge }}">{{ $procurement->salesOrder->status_label }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                @else
                                    <p class="text-muted mb-0">Not linked to any sales order.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment History Card -->
                <div class="card shadow-sm mt-3 mb-4">
                    <div class="card-header bg-light fw-bold">
                        <i class="fas fa-history me-2 text-warning"></i>
                        Payment History ({{ $procurement->payments->count() }})
                    </div>
                    <div class="card-body p-0">
                        @if ($procurement->payments->count() > 0)
                            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        @foreach ($procurement->payments()->latest()->take(5)->get() as $payment)
                                            <tr>
                                                <td class="py-1">
                                                    <div class="small fw-semibold text-danger">
                                                        ₹{{ number_format($payment->amount, 2) }}</div>
                                                    <div class="small text-muted">
                                                        {{ $payment->payment_date->format('d M Y') }}</div>
                                                    <span
                                                        class="badge bg-{{ strtolower(str_replace(' ', '-', $payment->mode_label)) }} px-2 py-1 small">
                                                        {{ $payment->mode_label }}
                                                    </span>
                                                    @if ($payment->details)
                                                        <div class="small text-muted">
                                                            {{ Str::limit($payment->details, 40) }}</div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                                <p class="mb-0">No payments recorded</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Section for Vendor -->

                @if ($procurement->status !== 'cancelled' && $procurement->outstanding_amount > 0)
                    <div class="card shadow-sm mt-3 mb-4">
                        <div class="card-header bg-warning bg-opacity-25">
                            <h6 class="mb-0 fw-bold text-warning">Record Vendor Payment</h6>
                        </div>
                        <div class="card-body py-3">
                            <a href="{{ route('procurements.payment-entry', $procurement) }}"
                                class="btn btn-warning w-100">
                                <i class="fas fa-money-bill-wave me-2"></i>Record Payment
                            </a>
                            <div class="text-center mt-2">
                                <small class="text-muted">Outstanding:
                                    ₹{{ number_format($procurement->outstanding_amount, 2) }}</small>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-8">

                        <div class="card shadow-sm mb-4">
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
                                                <th>Received</th>
                                                <th>Pending</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($procurement->items as $item)
                                                <tr>
                                                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                                    <td class="fw-bold">₹{{ number_format($item->subtotal, 2) }}</td>
                                                    <td>
                                                        <span
                                                            class="badge {{ $item->is_fully_received ? 'bg-success' : 'bg-warning' }}">
                                                            {{ $item->received_quantity }} / {{ $item->quantity }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $item->pending_quantity }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if ($procurement->attachments && count($procurement->attachments))
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-light fw-bold">
                                    <i class="fas fa-paperclip me-2"></i>Attachments
                                    ({{ count($procurement->attachments) }})
                                </div>
                                <div class="card-body">
                                    @foreach ($procurement->attachments as $attachment)
                                        <a href="{{ Storage::url($attachment) }}"
                                            class="btn btn-outline-primary btn-sm w-100 mb-2" target="_blank">
                                            <i class="fas fa-download me-1"></i>{{ basename($attachment) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light fw-bold">Shipping &amp; Notes</div>
                            <div class="card-body text-white">
                                <h6 class="fw-bold">Shipping Address</h6>
                                <p class="">
                                    {{ $procurement->shipping_address ?? 'No shipping address provided.' }}</p>
                                <hr>
                                <h6 class="fw-bold">Notes</h6>
                                <p class="">{{ $procurement->notes ?? 'No notes.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Receipt Transaction Log -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light fw-bold">
                                <i class="fas fa-history me-2"></i>Receipt Transaction Log
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Product</th>
                                                <th>Qty Received</th>
                                                <th>Date &amp; Time</th>
                                                <th>Received By</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $hasReceipts = false; @endphp
                                            @foreach ($procurement->items as $item)
                                                @foreach ($item->receipts as $receipt)
                                                    @php $hasReceipts = true; @endphp
                                                    <tr>
                                                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                                                        <td><span
                                                                class="badge bg-success">{{ $receipt->quantity_received }}</span>
                                                        </td>
                                                        <td>{{ $receipt->received_at->format('Y-m-d H:i') }}</td>
                                                        <td>{{ $receipt->user->name ?? 'N/A' }}</td>
                                                        <td>{{ $receipt->notes ?? '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                            @if (!$hasReceipts)
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">No receipt
                                                        transactions recorded yet.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
