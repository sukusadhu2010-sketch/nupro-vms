@extends('layouts.app')

@section('title', 'Sales Orders')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-shopping-cart me-2 text-success"></i>
                            Sales Orders
                        </h4>
                        <p class="text-muted mb-0">Manage and track sales orders</p>
                    </div>
                    <a href="{{ route('sales-orders.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>New Sales Order
                    </a>
                </div>

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

                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Expected Delivery</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($salesOrders as $order)
                                        <tr>
                                            <td><span
                                                    class="badge bg-primary px-3 py-2 rounded-pill">{{ $order->order_number }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $order->customer->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $order->customer->email ?? '' }}</small>
                                            </td>
                                            <td><strong
                                                    class="text-success">₹{{ number_format($order->total_amount, 2) }}</strong>
                                            </td>
                                            <td><span
                                                    class="badge {{ $order->status_badge }} px-3 py-2">{{ $order->status_label }}</span>
                                            </td>
                                            <td>{{ $order->expected_delivery_date?->format('Y-m-d') ?? '—' }}</td>
                                            <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('sales-orders.show', $order) }}"
                                                        class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if ($order->status !== 'delivered')
                                                        <a href="{{ route('sales-orders.edit', $order) }}"
                                                            class="btn btn-outline-secondary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    <form action="{{ route('sales-orders.destroy', $order) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">No sales orders found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($salesOrders->hasPages())
                        <div class="card-footer">
                            {{ $salesOrders->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
