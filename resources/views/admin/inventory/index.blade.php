@extends('layouts.app')

@section('title', 'Inventory Ledger')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-boxes me-2 text-primary"></i>Inventory Ledger
            </h4>
            <p class="text-muted mb-0">Track item stock movements against Purchase Orders, GRNs, issues, and adjustments</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.po-tracking') }}" class="btn btn-outline-info">
                <i class="fas fa-chart-pie me-1"></i>PO Consumption Tracking
            </a>
            <a href="{{ route('inventory.export.excel', request()->all()) }}" class="btn btn-outline-success">
                <i class="fas fa-file-excel me-1"></i>Excel Export
            </a>
            <a href="{{ route('inventory.print', request()->all()) }}" target="_blank" class="btn btn-outline-secondary">
                <i class="fas fa-print me-1"></i>Print / PDF
            </a>
            @if (auth()->user()->canAccess('inventory-ledger', 'create'))
                <a href="{{ route('inventory-ledger.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Record Transaction
                </a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-success border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Total Stock In (All Time)</span>
                    <h4 class="fw-bold mb-0 text-success mt-1">{{ number_format($metrics['total_qty_in'], 3) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-danger border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Total Stock Out (All Time)</span>
                    <h4 class="fw-bold mb-0 text-danger mt-1">{{ number_format($metrics['total_qty_out'], 3) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Current Stock on Hand</span>
                    <h4 class="fw-bold mb-0 text-primary mt-1">{{ number_format($metrics['current_stock_units'], 3) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-warning border-4">
                <div class="card-body">
                    <span class="text-muted small fw-semibold">Active Inventory SKUs</span>
                    <h4 class="fw-bold mb-0 text-warning mt-1">{{ $metrics['tracked_items_count'] }} Items</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('inventory-ledger.index') }}" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Transaction Type</label>
                    <select name="transaction_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="po" {{ request('transaction_type') == 'po' ? 'selected' : '' }}>PO (Ordered)</option>
                        <option value="grn" {{ request('transaction_type') == 'grn' ? 'selected' : '' }}>GRN (Stock In)</option>
                        <option value="issue" {{ request('transaction_type') == 'issue' ? 'selected' : '' }}>Issue (Stock Out)</option>
                        <option value="transfer" {{ request('transaction_type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="adjustment" {{ request('transaction_type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Product / Item</label>
                    <select name="product_id" class="form-select form-select-sm select2">
                        <option value="">All Products</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->sku ? "[{$p->sku}] " : '' }}{{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Vendor</label>
                    <select name="vendor_id" class="form-select form-select-sm select2">
                        <option value="">All Vendors</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Purchase Order (PO)</label>
                    <select name="purchase_order_id" class="form-select form-select-sm select2">
                        <option value="">All POs</option>
                        @foreach ($purchaseOrders as $po)
                            <option value="{{ $po->id }}" {{ request('purchase_order_id') == $po->id ? 'selected' : '' }}>
                                {{ $po->po_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100" title="Apply Filter"><i class="fas fa-filter"></i></button>
                    <a href="{{ route('inventory-ledger.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="fas fa-redo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 110px;">Date</th>
                            <th>Transaction Type</th>
                            <th>Reference No</th>
                            <th>PO No</th>
                            <th>Vendor</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th class="text-end">Qty In</th>
                            <th class="text-end">Qty Out</th>
                            <th class="text-end">Balance Qty</th>
                            <th>Unit</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ledgers as $l)
                            <tr>
                                <td class="small">{{ $l->transaction_date->format('d M Y') }}</td>
                                <td>
                                    @php
                                        $typeBadge = match($l->transaction_type) {
                                            'grn' => 'bg-success',
                                            'issue' => 'bg-danger',
                                            'transfer' => 'bg-info text-dark',
                                            'adjustment' => 'bg-warning text-dark',
                                            default => 'bg-primary',
                                        };
                                    @endphp
                                    <span class="badge {{ $typeBadge }}">
                                        {{ strtoupper($l->transaction_type) }}
                                    </span>
                                </td>
                                <td class="font-monospace small fw-semibold">{{ $l->reference_no ?: '—' }}</td>
                                <td class="small">
                                    @if ($l->purchaseOrder)
                                        <a href="{{ route('procurements.show', $l->purchaseOrder) }}" class="text-decoration-none">
                                            {{ $l->purchaseOrder->po_number }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small">{{ $l->vendor?->name ?? '—' }}</td>
                                <td class="font-monospace small">{{ $l->product?->sku ?? '—' }}</td>
                                <td class="fw-semibold">
                                    <a href="{{ route('reports.inventory.item-ledger', ['product_id' => $l->product_id]) }}" class="text-decoration-none">
                                        {{ $l->product?->name }}
                                    </a>
                                </td>
                                <td class="text-end font-monospace {{ $l->qty_in > 0 ? 'text-success fw-bold' : 'text-muted' }}">
                                    {{ $l->qty_in > 0 ? '+' . number_format($l->qty_in, 3) : '—' }}
                                </td>
                                <td class="text-end font-monospace {{ $l->qty_out > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                    {{ $l->qty_out > 0 ? '-' . number_format($l->qty_out, 3) : '—' }}
                                </td>
                                <td class="text-end font-monospace fw-bold fs-6 text-primary">
                                    {{ number_format($l->balance_qty, 3) }}
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $l->unit ?: 'Nos' }}</span></td>
                                <td class="small text-muted text-truncate" style="max-width: 150px;">{{ $l->remarks ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">
                                    <i class="fas fa-boxes fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                    No inventory movement records found matching your filters.
                                </td>
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

