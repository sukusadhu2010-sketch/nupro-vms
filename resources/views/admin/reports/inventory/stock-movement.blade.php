@extends('layouts.app')

@section('title', 'Stock Movement Report')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-exchange-alt me-2 text-primary"></i>Stock Movement Summary Report
            </h4>
            <p class="text-muted mb-0">Product-wise Opening Balance, Inward, Outward, and Closing Balance for specified date window</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print Movement Report
        </button>
    </div>

    <!-- Date Filter -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.inventory.stock-movement') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i>Generate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Movement Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>SKU</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th class="text-end">Opening Stock</th>
                            <th class="text-end">Qty In (+)</th>
                            <th class="text-end">Qty Out (-)</th>
                            <th class="text-end">Closing Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $m)
                            <tr>
                                <td class="font-monospace small">{{ $m['sku'] ?: '—' }}</td>
                                <td class="fw-semibold">
                                    <a href="{{ route('reports.inventory.item-ledger', ['product_id' => $m['product_id']]) }}" class="text-decoration-none">
                                        {{ $m['name'] }}
                                    </a>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $m['category'] }}</span></td>
                                <td>{{ $m['unit'] }}</td>
                                <td class="text-end font-monospace">{{ number_format($m['opening'], 3) }}</td>
                                <td class="text-end font-monospace text-success">{{ $m['qty_in'] > 0 ? '+' . number_format($m['qty_in'], 3) : '—' }}</td>
                                <td class="text-end font-monospace text-danger">{{ $m['qty_out'] > 0 ? '-' . number_format($m['qty_out'], 3) : '—' }}</td>
                                <td class="text-end font-monospace fw-bold fs-6 text-primary">{{ number_format($m['closing'], 3) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No stock movement recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

