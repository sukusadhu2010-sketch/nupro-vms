@extends('layouts.app')

@section('title', 'Customer Ledger Statement')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-balance-scale me-2 text-primary"></i>Customer Ledger Account
            </h4>
            <p class="text-muted mb-0">Running financial account statement showing billings (debits) and receipts (credits)</p>
        </div>
        @if ($selectedCustomer)
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print me-1"></i>Print Statement
            </button>
        @endif
    </div>

    <!-- Customer Selection Form -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.invoices.customer-ledger') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Select Customer Account</label>
                    <select name="customer_id" class="form-select select2" onchange="this.form.submit()">
                        <option value="">-- Choose Customer --</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>View Ledger</button>
                </div>
            </form>
        </div>
    </div>

    @if ($selectedCustomer)
        <!-- Ledger Header & Summary -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-light p-3">
                    <span class="small text-muted fw-semibold">Total Invoiced (Debit)</span>
                    <h5 class="fw-bold mb-0 text-dark">₹{{ number_format($summary['total_debit'], 2) }}</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-light p-3">
                    <span class="small text-muted fw-semibold">Total Paid (Credit)</span>
                    <h5 class="fw-bold mb-0 text-success">₹{{ number_format($summary['total_credit'], 2) }}</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-light p-3">
                    <span class="small text-muted fw-semibold">Closing Balance Due</span>
                    <h5 class="fw-bold mb-0 {{ $summary['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                        ₹{{ number_format($summary['balance'], 2) }} {{ $summary['balance'] > 0 ? 'Dr' : 'Cr' }}
                    </h5>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold">{{ $selectedCustomer->name }}</h5>
                    <small class="text-muted">GST: {{ $selectedCustomer->gstin ?: 'Unregistered' }}</small>
                </div>
                <span class="badge bg-light text-dark border">
                    Statement Period: Inception to Present
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Transaction Type</th>
                                <th>Reference / Doc No</th>
                                <th class="text-end">Debit (₹)</th>
                                <th class="text-end">Credit (₹)</th>
                                <th class="text-end">Running Balance (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ledgerEntries as $entry)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge {{ $entry->type === 'Invoice' ? 'bg-primary' : 'bg-success' }}">
                                            {{ $entry->type }}
                                        </span>
                                    </td>
                                    <td class="font-monospace fw-semibold">{{ $entry->reference }}</td>
                                    <td class="text-end font-monospace">
                                        {{ $entry->debit > 0 ? '₹' . number_format($entry->debit, 2) : '—' }}
                                    </td>
                                    <td class="text-end font-monospace text-success">
                                        {{ $entry->credit > 0 ? '₹' . number_format($entry->credit, 2) : '—' }}
                                    </td>
                                    <td class="text-end font-monospace fw-bold {{ $entry->balance > 0 ? 'text-danger' : 'text-success' }}">
                                        ₹{{ number_format(abs($entry->balance), 2) }} {{ $entry->balance >= 0 ? 'Dr' : 'Cr' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        No billing or payment activity recorded for this customer yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm border-0 py-5 text-center text-muted">
            <i class="fas fa-book-reader fa-3x mb-3 text-secondary opacity-50"></i>
            <h5>Select a customer from the dropdown above to view their financial ledger</h5>
        </div>
    @endif
</div>
@endsection

