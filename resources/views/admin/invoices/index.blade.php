@extends('layouts.app')

@section('title', 'Sales Invoices')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Invoice Management
            </h4>
            <p class="text-muted mb-0">Manage Tax Invoices, Proforma Invoices, Commercial Invoices & billing records</p>
        </div>
        @if (auth()->user()->canAccess('invoices', 'create'))
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create New Invoice
            </a>
        @endif
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

    <!-- Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Total Invoiced</span>
                    <h4 class="fw-bold mb-0 text-primary mt-1">₹{{ number_format($metrics['total_invoiced'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-success border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Total Received</span>
                    <h4 class="fw-bold mb-0 text-success mt-1">₹{{ number_format($metrics['total_paid'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-danger border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Total Outstanding</span>
                    <h4 class="fw-bold mb-0 text-danger mt-1">₹{{ number_format($metrics['total_outstanding'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-warning border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Pending Approvals</span>
                    <h4 class="fw-bold mb-0 text-warning mt-1">{{ $metrics['count_pending'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('invoices.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Invoice #, PO #, Customer..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Customer</label>
                    <select name="customer_id" class="form-select form-select-sm">
                        <option value="">All Customers</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="tax" {{ request('type') == 'tax' ? 'selected' : '' }}>Tax Invoice</option>
                        <option value="proforma" {{ request('type') == 'proforma' ? 'selected' : '' }}>Proforma Invoice</option>
                        <option value="commercial" {{ request('type') == 'commercial' ? 'selected' : '' }}>Commercial Invoice</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100" title="Apply Filter">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>SO / PO Reference</th>
                            <th class="text-end">Grand Total</th>
                            <th class="text-end">Paid Amount</th>
                            <th class="text-end">Balance Due</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $inv)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $inv) }}" class="fw-bold text-decoration-none">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td class="small">{{ $inv->invoice_date->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $inv->customer?->name ?? '—' }}</div>
                                    @if ($inv->customer_gst)
                                        <small class="text-muted">GST: {{ $inv->customer_gst }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $inv->type_label }}
                                    </span>
                                </td>
                                <td class="small">
                                    @if ($inv->salesOrder)
                                        <div><i class="fas fa-shopping-bag me-1 text-muted"></i>{{ $inv->salesOrder->order_number }}</div>
                                    @endif
                                    @if ($inv->po_number)
                                        <div class="text-muted">PO: {{ $inv->po_number }}</div>
                                    @endif
                                    @if (!$inv->salesOrder && !$inv->po_number)
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">₹{{ number_format($inv->grand_total, 2) }}</td>
                                <td class="text-end text-success">₹{{ number_format($inv->paid_amount, 2) }}</td>
                                <td class="text-end fw-bold {{ $inv->outstanding > 0 ? 'text-danger' : 'text-muted' }}">
                                    ₹{{ number_format($inv->outstanding, 2) }}
                                </td>
                                <td>
                                    <span class="badge {{ $inv->status_badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $inv->status)) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('invoices.show', $inv) }}" class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if (auth()->user()->canAccess('invoices', 'print'))
                                            <a href="{{ route('invoices.print', $inv) }}" target="_blank" class="btn btn-outline-secondary" title="Print">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        @endif
                                        @if (auth()->user()->canAccess('invoices', 'edit') && in_array($inv->status, ['draft', 'pending']))
                                            <a href="{{ route('invoices.edit', $inv) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if (auth()->user()->canAccess('invoices', 'delete') && $inv->paid_amount == 0 && $inv->status === 'draft')
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteInvoice({{ $inv->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fas fa-file-invoice fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                    No invoices found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($invoices->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteInvoice(id) {
    if (!confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) return;
    fetch(`{{ url('invoices') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status) {
            window.location.reload();
        } else {
            alert(res.message || 'Failed to delete invoice');
        }
    })
    .catch(() => alert('Network error occurred'));
}
</script>
@endpush
@endsection
