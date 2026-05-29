@extends('layouts.app')

@section('title', 'Invoice Management')

@section('content')
    <div class="glass py-3">
        <div class="row">
            <div class="col-lg-12">
                <div class="card glass">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="fas fa-file-invoice me-2 text-info"></i>
                                    Invoice Management
                                </h4>
                                <p class="mb-0 text-muted small">Manage all invoices</p>
                            </div>
                            <a href="{{ route('invoices.create') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-plus me-2"></i>Create Invoice
                            </a>
                        </div>
                    </div>

                    <!-- Search & Filter -->
                    <div class="card-body border-bottom">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Search invoice # or customer..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent
                                    </option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid
                                    </option>
                                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>
                                        Partial
                                    </option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="customer_id" class="form-select form-select-sm">
                                    <option value="">All Customers</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}"
                                            {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->company }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="date_from" class="form-control form-control-sm"
                                    placeholder="From Date" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="date_to" class="form-control form-control-sm"
                                    placeholder="To Date" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-outline-info btn-sm w-100">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Invoices Table -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>Invoice #</th>
                                        <th>Customer</th>
                                        <th>Sales Order</th>
                                        <th>Date</th>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($invoices as $index => $invoice)
                                        <tr style="height: 150px">
                                            <td>
                                                <div class="fw-bold text-info">{{ $invoices->firstItem() + $index }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $invoice->invoice_number }}</div>
                                            </td>
                                            <td>
                                                <div>{{ $invoice->customer->company }}</div>
                                                <small class="text-muted">{{ $invoice->customer->name }}</small>
                                            </td>
                                            <td>
                                                @if ($invoice->salesOrder)
                                                    <a href="{{ route('sales-orders.show', $invoice->salesOrder) }}"
                                                        class="text-decoration-none">
                                                        {{ $invoice->salesOrder->po_number ?? '—' }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $invoice->invoice_date->format('d M Y') }}
                                            </td>
                                            <td>
                                                @if ($invoice->due_date)
                                                    <span
                                                        class="{{ $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'text-danger' : '' }}">
                                                        {{ $invoice->due_date->format('d M Y') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-bold text-success">
                                                    ₹{{ number_format($invoice->total_amount, 2) }}
                                                </div>
                                            </td>
                                            <td>
                                                <div
                                                    class="{{ $invoice->paid_amount > 0 ? 'text-success' : 'text-muted' }}">
                                                    ₹{{ number_format($invoice->paid_amount, 2) }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $invoice->status_badge }} px-3 py-2 rounded-pill">
                                                    {{ $invoice->status_label }}
                                                </span>
                                            </td>
                                            <td>

                                                <ul class="inline-flex gap-1 m-0 p-0" style="list-style: none">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('invoices.show', $invoice) }}">
                                                            <i class="fas fa-eye me-2 text-info"></i>View
                                                        </a></li>
                                                    @if ($invoice->status !== 'cancelled' && $invoice->status !== 'paid')
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('invoices.edit', $invoice) }}">
                                                                <i class="fas fa-edit me-2 text-warning"></i>Edit
                                                            </a></li>
                                                    @endif
                                                    @if ($invoice->status === 'draft')
                                                        <li>
                                                            <form action="{{ route('invoices.sent', $invoice) }}"
                                                                method="POST">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-info">
                                                                    <i class="fas fa-paper-plane me-2"></i>Mark as Sent
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    @if (in_array($invoice->status, ['sent', 'partial']))
                                                        <li>
                                                            <a href="{{ route('invoices.payment-entry', $invoice) }}"
                                                                class="dropdown-item">
                                                                <i
                                                                    class="fas fa-money-bill-wave me-2 text-success"></i>Record
                                                                Payment
                                                            </a>
                                                        </li>
                                                    @endif
                                                    @if ($invoice->status !== 'cancelled')
                                                        <li>
                                                            <a class="dropdown-item text-danger"
                                                                href="{{ route('invoices.cancel-form', $invoice) }}">
                                                                <i class="fas fa-times-circle me-2"></i>Cancel
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>



                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-8">
                                                <i class="fas fa-file-invoice fa-3x text-muted mb-4 opacity-50"></i>
                                                <h5 class="text-muted mb-3">No invoices found</h5>
                                                <p class="text-muted mb-4">Create your first invoice to get started</p>
                                                <a href="{{ route('invoices.create') }}" class="btn btn-info px-4">
                                                    <i class="fas fa-plus me-2"></i>Create Invoice
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if ($invoices->hasPages())
                        <div class="card-footer py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of
                                    {{ $invoices->total() }} invoices
                                </div>
                                {{ $invoices->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
