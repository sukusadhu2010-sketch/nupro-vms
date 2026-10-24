@extends('layouts.app')

@section('title', 'Inventory Ledger Report')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-boxes me-2 text-primary"></i>Inventory Ledger Report
            </h4>
            <p class="text-muted mb-0">Complete historical inventory movements and running balances</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print Report
        </button>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.inventory.ledger') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Product</label>
                    <select name="product_id" class="form-select form-select-sm select2">
                        <option value="">All Products</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Transaction Type</label>
                    <select name="transaction_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="grn" {{ request('transaction_type') == 'grn' ? 'selected' : '' }}>GRN</option>
                        <option value="issue" {{ request('transaction_type') == 'issue' ? 'selected' : '' }}>Issue</option>
                        <option value="transfer" {{ request('transaction_type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="adjustment" {{ request('transaction_type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter"></i></button>
                    <a href="{{ route('reports.inventory.ledger') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th>PO No</th>
                            <th>Item Name</th>
                            <th class="text-end">Qty In</th>
                            <th class="text-end">Qty Out</th>
                            <th class="text-end">Balance Qty</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ledgers as $l)
                            <tr>
                                <td class="small">{{ $l->transaction_date->format('d/m/Y') }}</td>
                                <td><span class="badge bg-light text-dark border">{{ strtoupper($l->transaction_type) }}</span></td>
                                <td class="font-monospace small">{{ $l->reference_no ?: '—' }}</td>
                                <td>{{ $l->purchaseOrder?->po_number ?? '—' }}</td>
                                <td class="fw-semibold">{{ $l->product?->name }}</td>
                                <td class="text-end font-monospace text-success">{{ $l->qty_in > 0 ? '+' . number_format($l->qty_in, 3) : '—' }}</td>
                                <td class="text-end font-monospace text-danger">{{ $l->qty_out > 0 ? '-' . number_format($l->qty_out, 3) : '—' }}</td>
                                <td class="text-end font-monospace fw-bold text-primary">{{ number_format($l->balance_qty, 3) }}</td>
                                <td>{{ $l->unit }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No inventory ledger entries found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($ledgers->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $ledgers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

