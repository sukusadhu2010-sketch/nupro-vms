@extends('layouts.app')

@section('title', 'Edit Sales Order ' . $salesOrder->order_number)

@section('content')
    @php
        $isLocked = in_array($salesOrder->status, ['confirmed', 'processing', 'shipped', 'delivered']);
    @endphp
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            @if ($isLocked)
                                <i class="fas fa-lock me-2 text-danger"></i>
                                Sales Order {{ $salesOrder->order_number }}
                            @else
                                <i class="fas fa-edit me-2 text-primary"></i>
                                Edit Sales Order {{ $salesOrder->order_number }}
                            @endif
                        </h4>
                        <p class="text-muted mb-0">
                            @if ($isLocked)
                                This order is locked. PO Number and Job Number cannot be edited once status is Confirmed or
                                higher.
                            @else
                                Update order status and details
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('sales-orders.show', $salesOrder) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>

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
                        @if ($isLocked)
                            <div class="alert alert-warning">
                                <i class="fas fa-lock me-2"></i>
                                <strong>Locked:</strong> PO Number and Job Number cannot be edited once status is Confirmed
                                or higher.
                            </div>
                            <form action="{{ route('sales-orders.update', $salesOrder) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span
                                                class="badge {{ $salesOrder->status_badge }}">{{ $salesOrder->status_label }}</span>
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
                                        <th>Shipping Address</th>
                                        <td>{{ $salesOrder->shipping_address ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Expected Delivery</th>
                                        <td>{{ $salesOrder->expected_delivery_date?->format('Y-m-d') ?? '—' }}</td>
                                    </tr>
                                </table>
                                @if ($salesOrder->status !== 'delivered')
                                    <div class="mb-3">
                                        <label for="status" class="form-label fw-bold">Update Status</label>
                                        <select name="status" id="status" class="form-select" required>
                                            @if ($salesOrder->status === 'confirmed')
                                                <option value="confirmed" selected>Confirmed</option>
                                                <option value="processing">Processing</option>
                                                <option value="shipped">Shipped</option>
                                                <option value="delivered">Delivered</option>
                                            @elseif($salesOrder->status === 'processing')
                                                <option value="processing" selected>Processing</option>
                                                <option value="shipped">Shipped</option>
                                                <option value="delivered">Delivered</option>
                                            @elseif($salesOrder->status === 'shipped')
                                                <option value="shipped" selected>Shipped</option>
                                                <option value="delivered">Delivered</option>
                                            @else
                                                @foreach (['confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $status)
                                                    <option value="{{ $status }}"
                                                        {{ $salesOrder->status === $status ? 'selected' : '' }}>
                                                        {{ ucfirst($status) }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('sales-orders.show', $salesOrder) }}"
                                        class="btn btn-outline-secondary">Cancel</a>
                                    @if ($salesOrder->status !== 'delivered')
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Status
                                        </button>
                                    @endif
                                </div>
                            </form>
                        @else
                            <form action="{{ route('sales-orders.update', $salesOrder) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="status" class="form-label fw-bold">Status</label>
                                    <select name="status" id="status" class="form-select" required>
                                        @foreach (['draft', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $status)
                                            <option value="{{ $status }}"
                                                {{ $salesOrder->status === $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="po_number" class="form-label fw-bold">PO Number</label>
                                        <input type="text" name="po_number" id="po_number" class="form-control"
                                            value="{{ old('po_number', $salesOrder->po_number) }}"
                                            placeholder="Enter PO number">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="job_number" class="form-label fw-bold">Job Number</label>
                                        <input type="text" name="job_number" id="job_number" class="form-control"
                                            value="{{ old('job_number', $salesOrder->job_number) }}"
                                            placeholder="Enter job number">
                                    </div>

                                    <div class="mb-3">
                                        <label for="shipping_address" class="form-label fw-bold">Shipping Address</label>
                                        <textarea name="shipping_address" id="shipping_address" rows="3" class="form-control">{{ old('shipping_address', $salesOrder->shipping_address) }}</textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="expected_delivery_date" class="form-label fw-bold">Expected Delivery
                                            Date</label>
                                        <input type="date" name="expected_delivery_date" id="expected_delivery_date"
                                            class="form-control"
                                            value="{{ old('expected_delivery_date', $salesOrder->expected_delivery_date?->format('Y-m-d')) }}">
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('sales-orders.show', $salesOrder) }}"
                                            class="btn btn-outline-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Order
                                        </button>
                                    </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endsection
