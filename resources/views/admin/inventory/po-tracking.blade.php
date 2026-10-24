@extends('layouts.app')

@section('title', 'PO Consumption Tracking')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-chart-pie me-2 text-primary"></i>PO Consumption & Stock Fulfillment Tracking
            </h4>
            <p class="text-muted mb-0">Track item-wise PO quantity, goods received, pending fulfillment, consumed quantity, and available balances</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory-ledger.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Ledger
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print me-1"></i>Print Report
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('inventory.po-tracking') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Search PO #</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Search PO Number..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
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
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tracking Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>PO Number</th>
                            <th>PO Date</th>
                            <th>Vendor</th>
                            <th>Item Description</th>
                            <th class="text-end">PO Qty</th>
                            <th class="text-end">Received Qty</th>
                            <th class="text-end">Pending Qty</th>
                            <th class="text-end">Consumed Qty</th>
                            <th class="text-end">Available Qty</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pos as $po)
                            @foreach ($po->items as $idx => $item)
                                @php
                                    $m = $poMetrics[$po->id][$item->id] ?? [
                                        'ordered' => (float) $item->quantity,
                                        'received' => (float) $item->received_quantity,
                                        'pending' => max(0, $item->quantity - $item->received_quantity),
                                        'consumed' => 0,
                                        'available' => (float) $item->received_quantity,
                                    ];
                                @endphp
                                <tr>
                                    @if ($idx === 0)
                                        <td rowspan="{{ $po->items->count() }}" class="fw-bold font-monospace bg-white border-end">
                                            <a href="{{ route('procurements.show', $po) }}" class="text-decoration-none">
                                                {{ $po->po_number }}
                                            </a>
                                        </td>
                                        <td rowspan="{{ $po->items->count() }}" class="small bg-white border-end">
                                            {{ $po->order_date?->format('d/m/Y') ?? '—' }}
                                        </td>
                                        <td rowspan="{{ $po->items->count() }}" class="small bg-white border-end">
                                            {{ $po->vendor?->name ?? '—' }}
                                        </td>
                                    @endif
                                    <td>
                                        <div class="fw-semibold">{{ $item->product?->name }}</div>
                                        <small class="text-muted font-monospace">{{ $item->product?->sku }}</small>
                                    </td>
                                    <td class="text-end font-monospace fw-semibold">
                                        {{ number_format($m['ordered'], 2) }}
                                    </td>
                                    <td class="text-end font-monospace text-success fw-semibold">
                                        {{ number_format($m['received'], 2) }}
                                    </td>
                                    <td class="text-end font-monospace {{ $m['pending'] > 0 ? 'text-warning fw-bold' : 'text-muted' }}">
                                        {{ number_format($m['pending'], 2) }}
                                    </td>
                                    <td class="text-end font-monospace text-danger">
                                        {{ number_format($m['consumed'], 2) }}
                                    </td>
                                    <td class="text-end font-monospace fw-bold fs-6 text-primary">
                                        {{ number_format($m['available'], 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if ($m['pending'] == 0)
                                            <span class="badge bg-success">Fully Received</span>
                                        @elseif ($m['received'] > 0)
                                            <span class="badge bg-warning text-dark">Partially Received</span>
                                        @else
                                            <span class="badge bg-secondary">Pending Receipt</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    No purchase orders found matching your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pos->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $pos->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

