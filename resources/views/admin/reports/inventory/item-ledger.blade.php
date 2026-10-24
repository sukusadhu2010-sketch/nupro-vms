@extends('layouts.app')

@section('title', 'Item Ledger Report')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-boxes me-2 text-primary"></i>Item-wise Inventory Ledger
            </h4>
            <p class="text-muted mb-0">Detailed historical transactional drilldown and running balance for any specific item</p>
        </div>
        @if ($selectedProduct)
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print me-1"></i>Print Item Ledger
            </button>
        @endif
    </div>

    <!-- Product Selection Form -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.inventory.item-ledger') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Select Item to Inspect</label>
                    <select name="product_id" class="form-select select2" onchange="this.form.submit()">
                        <option value="">-- Choose Item --</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->sku ? "[{$p->sku}] " : '' }}{{ $p->name }}
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

    @if ($selectedProduct)
        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-light p-3">
                    <span class="small text-muted fw-semibold">Total Stock Inward</span>
                    <h5 class="fw-bold mb-0 text-success">{{ number_format($summary['total_in'], 3) }} {{ $selectedProduct->unit?->short_name ?? 'Nos' }}</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-light p-3">
                    <span class="small text-muted fw-semibold">Total Stock Outward</span>
                    <h5 class="fw-bold mb-0 text-danger">{{ number_format($summary['total_out'], 3) }} {{ $selectedProduct->unit?->short_name ?? 'Nos' }}</h5>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-light p-3">
                    <span class="small text-muted fw-semibold">Current On-Hand Balance</span>
                    <h5 class="fw-bold mb-0 text-primary">{{ number_format($summary['current_balance'], 3) }} {{ $selectedProduct->unit?->short_name ?? 'Nos' }}</h5>
                </div>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold">{{ $selectedProduct->name }}</h5>
                    <small class="text-muted font-monospace">SKU: {{ $selectedProduct->sku ?: '—' }} &bull; Category: {{ $selectedProduct->category?->name ?? '—' }}</small>
                </div>
                <span class="badge bg-primary fs-6">
                    Balance: {{ number_format($summary['current_balance'], 3) }} {{ $selectedProduct->unit?->short_name ?? 'Nos' }}
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Transaction Type</th>
                                <th>Reference</th>
                                <th>Linked PO</th>
                                <th>Party / Vendor</th>
                                <th class="text-end">Qty In (+)</th>
                                <th class="text-end">Qty Out (-)</th>
                                <th class="text-end">Running Balance</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ledgerEntries as $entry)
                                <tr>
                                    <td>{{ $entry->transaction_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ strtoupper($entry->transaction_type) }}
                                        </span>
                                    </td>
                                    <td class="font-monospace small">{{ $entry->reference_no ?: '—' }}</td>
                                    <td>{{ $entry->purchaseOrder?->po_number ?? '—' }}</td>
                                    <td>{{ $entry->vendor?->name ?? '—' }}</td>
                                    <td class="text-end font-monospace text-success">
                                        {{ $entry->qty_in > 0 ? '+' . number_format($entry->qty_in, 3) : '—' }}
                                    </td>
                                    <td class="text-end font-monospace text-danger">
                                        {{ $entry->qty_out > 0 ? '-' . number_format($entry->qty_out, 3) : '—' }}
                                    </td>
                                    <td class="text-end font-monospace fw-bold fs-6 text-primary">
                                        {{ number_format($entry->balance_qty, 3) }}
                                    </td>
                                    <td class="small text-muted">{{ $entry->remarks ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        No stock transactions recorded for this item yet.
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
            <i class="fas fa-boxes fa-3x mb-3 text-secondary opacity-50"></i>
            <h5>Select an item from the dropdown above to view its chronological movement ledger</h5>
        </div>
    @endif
</div>
@endsection

