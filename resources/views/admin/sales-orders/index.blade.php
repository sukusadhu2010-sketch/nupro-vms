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

                <!-- Advanced Filters (Collapsible) -->
                <div class="card shadow-sm border-0 rounded-3 mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center"
                        data-bs-toggle="collapse" data-bs-target="#filtersCard" role="button"
                        aria-expanded="true">
                        <span class="fw-bold"><i class="fas fa-filter me-2 text-primary"></i>Advanced Filters</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="collapse show" id="filtersCard">
                        <div class="card-body">
                            <form id="filtersForm" method="GET" action="{{ route('sales-orders.index') }}">
                                <div class="row g-3">
                                    <!-- Date Range -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Date Type</label>
                                        <select name="date_field" class="form-select form-select-sm">
                                            <option value="customer_po_date" {{ ($filters['date_field'] ?? '') === 'customer_po_date' ? 'selected' : '' }}>PO Date</option>
                                            <option value="created_at" {{ ($filters['date_field'] ?? '') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">From Date</label>
                                        <input type="date" name="date_from" class="form-control form-control-sm"
                                            value="{{ $filters['date_from'] ?? '' }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">To Date</label>
                                        <input type="date" name="date_to" class="form-control form-control-sm"
                                            value="{{ $filters['date_to'] ?? '' }}">
                                    </div>
                                    <!-- Status -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Status</label>
                                        <select name="status" class="form-select form-select-sm">
                                            @foreach (['all' => 'All', 'draft' => 'Pending', 'confirmed' => 'Confirmed', 'processing' => 'In Progress', 'shipped' => 'Shipped', 'delivered' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                                                <option value="{{ $value }}" {{ ($filters['status'] ?? 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- Customer -->
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Customer</label>
                                        <select name="customer_id" id="customerFilter" class="form-select form-select-sm" data-placeholder="Search customer...">
                                            <option value=""></option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ ($filters['customer_id'] ?? '') == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- Project -->
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Project</label>
                                        <select name="product_id" id="projectFilter" class="form-select form-select-sm" data-placeholder="Search project...">
                                            <option value=""></option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}" {{ ($filters['product_id'] ?? '') == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- Job No -->
                                    <!-- <div class="col-md-2">
                                        <label class="form-label small fw-bold">Our Job No</label>
                                        <input type="text" name="job_number" class="form-control form-control-sm" placeholder="Search..."
                                            value="{{ $filters['job_number'] ?? '' }}">
                                    </div> -->
                                    <!-- PO No -->
                                    <!-- <div class="col-md-2">
                                        <label class="form-label small fw-bold">PO No</label>
                                        <input type="text" name="po_no" class="form-control form-control-sm" placeholder="Search..."
                                            value="{{ $filters['po_no'] ?? '' }}">
                                    </div> -->
                                    <!-- Remarks -->
                                    <!-- <div class="col-md-2">
                                        <label class="form-label small fw-bold">Remarks</label>
                                        <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Keyword..."
                                            value="{{ $filters['remarks'] ?? '' }}">
                                    </div> -->
                                    <!-- Actions -->
                                    <div class="col-md-4 d-flex align-items-end gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-search me-1"></i>Apply
                                        </button>
                                        <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary btn-sm w-100">
                                            <i class="fas fa-rotate-left me-1"></i>Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Toolbar: count + exports -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="badge bg-light text-dark border px-3 py-2">
                            <i class="fas fa-list-ol me-1 text-primary"></i>
                            Total Records: <strong>{{ $totalRecords }}</strong>
                        </span>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('sales-orders.export.excel', request()->only(['date_from', 'date_to', 'date_field', 'status', 'customer_id', 'product_id', 'job_number', 'po_no', 'remarks'])) }}"
                            class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-excel me-2"></i>Export Excel
                        </a>
                        <a href="{{ route('sales-orders.export.pdf', request()->only(['date_from', 'date_to', 'date_field', 'status', 'customer_id', 'product_id', 'job_number', 'po_no', 'remarks'])) }}"
                            class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-file-pdf me-2"></i>Export PDF
                        </a>
                    </div>
                </div>

                <!-- Loading overlay -->
                <div id="loadingOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100"
                    style="background: rgba(0,0,0,0.35); z-index: 1050;">
                    <div class="position-absolute top-50 start-50 translate-middle text-white text-center">
                        <div class="spinner-border text-light mb-2" role="status" style="width: 3rem; height: 3rem;"></div>
                        <div class="fw-semibold">Please wait...</div>
                    </div>
                </div>

                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Job #</th>
                                        <th>Customer</th>
                                        <th>Customer PO Number</th>
                                        <th>Payment Mode</th>
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
                                            <td><small class="text-muted">{{ $order->job_number ?? '—' }}</small></td>
                                            <td>
                                                <div class="fw-bold">{{ $order->customer->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $order->customer->email ?? '' }}</small>
                                            </td>
                                            <td>{{ $order->customer_po_number ?? '—' }}</td>
                                            <td>{{ $order->payment_mode_label }}</td>
                                            <td><strong
                                                    class="text-success">{{ number_format($order->total_amount, 2) }}</strong>
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
                                            <td colspan="10" class="text-center text-muted py-4">No sales orders found.
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Select2 searchable dropdowns
        if (window.jQuery && window.jQuery.fn.select2) {
            jQuery('#customerFilter, #projectFilter').select2({
                width: '100%',
                allowClear: true,
                dropdownAutoWidth: true
            });
        }

        // Loading indicator during filter/apply and exports
        const overlay = document.getElementById('loadingOverlay');
        const showLoading = () => overlay && overlay.classList.remove('d-none');

        const filtersForm = document.getElementById('filtersForm');
        if (filtersForm) {
            filtersForm.addEventListener('submit', showLoading);
        }

        document.querySelectorAll('a[href*="sales-orders-export"]').forEach(link => {
            link.addEventListener('click', showLoading);
        });
    });
</script>
@endpush
