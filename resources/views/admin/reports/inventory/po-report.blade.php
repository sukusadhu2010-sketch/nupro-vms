@extends('layouts.app')

@section('title', 'PO Inventory Report')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-clipboard-list me-2 text-primary"></i>PO Inventory Fulfillment & Consumption Report
            </h4>
            <p class="text-muted mb-0">Track PO ordered quantities against warehouse GRN receipts and job consumptions</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print Report
        </button>
    </div>

    <!-- Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>PO Number</th>
                            <th>PO Date</th>
                            <th>Vendor</th>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th class="text-end">PO Qty</th>
                            <th class="text-end">Received Qty</th>
                            <th class="text-end">Pending Qty</th>
                            <th class="text-end">Consumed Qty</th>
                            <th class="text-end">Available Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $r)
                            <tr>
                                <td class="fw-semibold font-monospace">{{ $r['po_number'] }}</td>
                                <td class="small">{{ $r['po_date'] }}</td>
                                <td>{{ $r['vendor'] }}</td>
                                <td class="fw-semibold">{{ $r['product'] }}</td>
                                <td class="font-monospace small">{{ $r['sku'] ?: '—' }}</td>
                                <td class="text-end font-monospace">{{ number_format($r['po_qty'], 2) }}</td>
                                <td class="text-end font-monospace text-success">{{ number_format($r['received_qty'], 2) }}</td>
                                <td class="text-end font-monospace {{ $r['pending_qty'] > 0 ? 'text-warning fw-bold' : 'text-muted' }}">
                                    {{ number_format($r['pending_qty'], 2) }}
                                </td>
                                <td class="text-end font-monospace text-danger">{{ number_format($r['consumed_qty'], 2) }}</td>
                                <td class="text-end font-monospace fw-bold text-primary">{{ number_format($r['available_qty'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">No purchase orders found.</td>
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

