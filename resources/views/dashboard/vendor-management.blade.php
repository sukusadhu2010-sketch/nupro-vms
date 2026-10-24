@extends('layouts.app')

@section('title', 'Vendor Management Dashboard')

@push('styles')
<style>
    .kpi-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 0.75rem;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
    }
    .stat-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
        border-radius: 9999px;
    }
    @media print {
        #sidebar, nav.navbar, .no-print {
            display: none !important;
        }
        main {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            page-break-inside: avoid;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3 px-md-4">

    <!-- Header & Quick Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="mb-1 fw-bold text-dark">
                <i class="fas fa-chart-pie me-2 text-primary"></i>Vendor Management Dashboard
            </h3>
            <p class="text-muted mb-0 small">
                Real-time overview of vendor performance, procurements, payments, deliveries, and inventory movements.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 no-print">
            <a href="{{ route('dashboard.export-vendors') }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-excel me-1"></i>Export Vendors to CSV
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i>Print Dashboard
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm border" title="Refresh Dashboard">
                <i class="fas fa-sync-alt"></i>
            </a>
        </div>
    </div>

    <!-- Global Dashboard Filters -->
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body p-3">
            <form action="{{ route('dashboard') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Date Range (From)</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Date Range (To)</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Filter by Vendor</label>
                    <select name="vendor_id" class="form-select form-select-sm">
                        <option value="">-- All Vendors --</option>
                        @foreach ($allVendors as $v)
                            <option value="{{ $v->id }}" {{ $vendorId == $v->id ? 'selected' : '' }}>
                                {{ $v->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">PO Status</label>
                    <select name="po_status" class="form-select form-select-sm">
                        <option value="">-- All Statuses --</option>
                        <option value="draft" {{ $poStatus == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ $poStatus == 'pending' ? 'selected' : '' }}>Pending / Sent</option>
                        <option value="approved" {{ $poStatus == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="partially_received" {{ $poStatus == 'partially_received' ? 'selected' : '' }}>Partially Received</option>
                        <option value="received" {{ $poStatus == 'received' ? 'selected' : '' }}>Completed / Received</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100" title="Apply Filter">
                        <i class="fas fa-filter"></i>
                    </button>
                    @if ($dateFrom || $dateTo || $vendorId || $poStatus)
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-danger btn-sm" title="Clear Filters">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Alerts & Critical Notifications -->
    @if (!empty($alerts))
    <div class="row g-2 mb-4">
        @foreach ($alerts as $alt)
            <div class="col-md-6 col-lg-3">
                <div class="alert mb-0 p-2 border-0 shadow-sm d-flex align-items-center gap-2
                    {{ $alt['type'] === 'critical' ? 'alert-danger' : ($alt['type'] === 'warning' ? 'alert-warning' : 'alert-success') }}">
                    <div class="p-2 rounded-circle {{ $alt['type'] === 'critical' ? 'bg-danger text-white' : ($alt['type'] === 'warning' ? 'bg-warning text-dark' : 'bg-success text-white') }}">
                        <i class="fas {{ $alt['icon'] }} fa-fw"></i>
                    </div>
                    <div class="overflow-hidden flex-grow-1">
                        <div class="fw-bold small text-truncate">{{ $alt['title'] }}</div>
                        <div class="text-muted text-truncate" style="font-size: 0.72rem;">{{ $alt['message'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <!-- 1. TOP KPI SECTION (4 PILLARS) -->
    <div class="row g-3 mb-4">
        <!-- Vendor Pillar -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 kpi-card border-start border-primary border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Vendor Network</span>
                        <div class="p-2 bg-primary-subtle rounded text-primary">
                            <i class="fas fa-building fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h2 class="fw-bold mb-0 text-dark">{{ $vendorStats['total'] }}</h2>
                        <span class="text-muted small">Total Vendors</span>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top text-muted small" style="font-size: 0.75rem;">
                        <span><i class="fas fa-check-circle text-success me-1"></i>Active: <strong>{{ $vendorStats['active'] }}</strong></span>
                        <span><i class="fas fa-clock text-warning me-1"></i>Pending: <strong>{{ $vendorStats['pending'] }}</strong></span>
                        <span><i class="fas fa-ban text-danger me-1"></i>Blocked: <strong>{{ $vendorStats['inactive'] }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Pillar -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 kpi-card border-start border-info border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Purchase Orders</span>
                        <div class="p-2 bg-info-subtle rounded text-info">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h2 class="fw-bold mb-0 text-dark">{{ $purchaseStats['total_pos'] }}</h2>
                        <span class="text-muted small">POs Placed</span>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top text-muted small" style="font-size: 0.75rem;">
                        <span><i class="fas fa-spinner text-primary me-1"></i>Open: <strong>{{ $purchaseStats['open_pos'] }}</strong></span>
                        <span><i class="fas fa-check-double text-success me-1"></i>Closed: <strong>{{ $purchaseStats['closed_pos'] }}</strong></span>
                        <span><i class="fas fa-times-circle text-danger me-1"></i>Cancelled: <strong>{{ $purchaseStats['cancelled_pos'] }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Pillar -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 kpi-card border-start border-success border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Procurement Spend</span>
                        <div class="p-2 bg-success-subtle rounded text-success">
                            <i class="fas fa-money-bill-wave fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h3 class="fw-bold mb-0 text-dark font-monospace">₹{{ number_format($financialStats['total_purchase_value'], 2) }}</h3>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top text-muted small" style="font-size: 0.75rem;">
                        <span>Paid: <strong class="text-success font-monospace">₹{{ number_format($financialStats['paid_amount'], 0) }}</strong></span>
                        <span>Pending: <strong class="text-danger font-monospace">₹{{ number_format($financialStats['pending_payments'], 0) }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Pillar -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 kpi-card border-start border-warning border-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold text-uppercase">Inventory &amp; GRN</span>
                        <div class="p-2 bg-warning-subtle rounded text-warning">
                            <i class="fas fa-boxes fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h2 class="fw-bold mb-0 text-dark">{{ $inventoryStats['total_grns'] }}</h2>
                        <span class="text-muted small">GRN Receipts</span>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top text-muted small" style="font-size: 0.75rem;">
                        <span>Stock Units: <strong>{{ number_format($inventoryStats['total_units'], 0) }}</strong></span>
                        <span>Low Stock: <strong class="{{ $inventoryStats['low_stock'] > 0 ? 'text-danger' : 'text-success' }}">{{ $inventoryStats['low_stock'] }}</strong></span>
                        <span>Value: <strong class="text-primary font-monospace">₹{{ number_format($inventoryStats['inventory_value'], 0) }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div class="card border-0 shadow-sm mb-4 no-print bg-white">
        <div class="card-body py-2 px-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="fw-bold text-muted small text-uppercase">
                    <i class="fas fa-bolt text-warning me-1"></i>Quick Actions:
                </span>
                <div class="d-flex flex-wrap gap-2">
                    @if (auth()->user()?->canAccess('vendors.create'))
                        <a href="{{ route('vendors.create') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>Add Vendor
                        </a>
                    @endif
                    @if (auth()->user()?->canAccess('procurements.create'))
                        <a href="{{ route('procurements.create') }}" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-truck me-1"></i>Create PO
                        </a>
                    @endif
                    @if (auth()->user()?->canAccess('inventory-ledger.create') || auth()->user()?->canAccess('inventory.create'))
                        <a href="{{ route('inventory-ledger.create', ['transaction_type' => 'grn']) }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-dolly me-1"></i>Record GRN
                        </a>
                    @endif
                    @if (auth()->user()?->canAccess('invoices.create'))
                        <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-file-invoice me-1"></i>Add Invoice
                        </a>
                    @endif
                    @if (auth()->user()?->canAccess('vendor-payments.create'))
                        <a href="{{ route('vendor-payments.create') }}" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-money-bill-wave me-1"></i>Record Payment
                        </a>
                    @endif
                    @if (auth()->user()?->canAccess('receipts.create'))
                        <a href="{{ route('receipts.create') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-receipt me-1"></i>Customer Receipt
                        </a>
                    @endif
                    @if (auth()->user()?->canAccess('inventory-ledger'))
                        <a href="{{ route('inventory-ledger.index') }}" class="btn btn-sm btn-light border">
                            <i class="fas fa-warehouse me-1"></i>Inventory Ledger
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- CHARTS SECTION: SPEND, PO STATUS, VENDOR STATUS, PAYMENT MODES -->
    <div class="row g-3 mb-4">
        <!-- Monthly Spend Analysis Chart -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-line text-primary me-2"></i>Monthly Purchase Spend Trend (Last 6 Months)
                    </h6>
                    <span class="badge bg-primary-subtle text-primary">In ₹ INR</span>
                </div>
                <div class="card-body p-3">
                    <canvas id="monthlySpendChart" height="230"></canvas>
                </div>
            </div>
        </div>

        <!-- Purchase Order Status Breakdown -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-pie text-info me-2"></i>PO Status Distribution
                    </h6>
                </div>
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                    <div style="height: 200px; width: 100%;">
                        <canvas id="poStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendor Network Status -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-check text-success me-2"></i>Vendor Status Breakdown
                    </h6>
                </div>
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                    <div style="height: 200px; width: 100%;">
                        <canvas id="vendorStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RECENT POs & RECENT GRNs -->
    <div class="row g-3 mb-4">
        <!-- Recent Purchase Orders (Latest 10) -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-clock text-danger me-2"></i>Recent Purchase Orders
                    </h6>
                    <a href="{{ route('procurements.index') }}" class="btn btn-sm btn-link text-decoration-none">
                        View All POs &rarr;
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>PO No</th>
                                    <th>Date</th>
                                    <th>Vendor</th>
                                    <th class="text-end">Amount (₹)</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentPurchaseOrders as $po)
                                    <tr>
                                        <td class="font-monospace fw-bold">
                                            <a href="{{ route('procurements.show', $po) }}" class="text-decoration-none">
                                                {{ $po->po_number }}
                                            </a>
                                        </td>
                                        <td>{{ $po->order_date?->format('d M Y') ?? '—' }}</td>
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width: 140px;">
                                                {{ $po->vendor?->name ?? '—' }}
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold font-monospace text-dark">
                                            ₹{{ number_format($po->total_amount, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $po->status_badge }}">
                                                {{ $po->status_label }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('procurements.show', $po) }}" class="btn btn-outline-secondary btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('procurements.print', $po) }}" target="_blank" class="btn btn-outline-dark btn-sm" title="Print">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                @if ($po->status !== 'received')
                                                    <a href="{{ route('procurements.edit', $po) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No purchase orders found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent GRN Goods Receipts (Latest 5) & Pending Approvals -->
        <div class="col-lg-5">
            <!-- Recent GRNs -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-dolly text-success me-2"></i>Recent Goods Receipts (GRN)
                    </h6>
                    <a href="{{ route('inventory-ledger.index', ['transaction_type' => 'grn']) }}" class="btn btn-sm btn-link text-decoration-none">
                        View Ledger &rarr;
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Ref / GRN</th>
                                    <th>Vendor</th>
                                    <th>Date</th>
                                    <th class="text-end">Qty In</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentGrns as $grn)
                                    <tr>
                                        <td class="font-monospace fw-bold text-primary">
                                            {{ $grn->reference_no ?: 'GRN-' . $grn->id }}
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 120px;">
                                                {{ $grn->vendor?->name ?? '—' }}
                                            </div>
                                        </td>
                                        <td class="small">{{ $grn->transaction_date?->format('d M') }}</td>
                                        <td class="text-end font-monospace fw-bold text-success">
                                            +{{ number_format($grn->qty_in, 2) }} {{ $grn->unit }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">
                                            No recent GRN inward entries recorded.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals Panel -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-tasks text-warning me-2"></i>Pending Approvals &amp; Action Items
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <div>
                                <i class="fas fa-truck text-danger me-2"></i>Pending Purchase Orders
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger rounded-pill">{{ $pendingApprovals['pos']->count() }}</span>
                                <a href="{{ route('procurements.index') }}" class="btn btn-xs btn-outline-secondary">Review</a>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <div>
                                <i class="fas fa-user-plus text-primary me-2"></i>New Vendor Approvals
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary rounded-pill">{{ $pendingApprovals['vendors']->count() }}</span>
                                <a href="{{ route('vendors.index') }}" class="btn btn-xs btn-outline-secondary">Review</a>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <div>
                                <i class="fas fa-file-invoice text-info me-2"></i>Draft / Pending Invoices
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-info rounded-pill">{{ $pendingApprovals['invoices']->count() }}</span>
                                <a href="{{ route('invoices.index') }}" class="btn btn-xs btn-outline-secondary">Review</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- INVENTORY MOVEMENT TRENDS & VENDOR PERFORMANCE SECTION -->
    <div class="row g-3 mb-4">
        <!-- Stock Movement Trend (Last 14 Days) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-exchange-alt text-success me-2"></i>Inventory Movement Trend (Stock In vs Stock Out - 14 Days)
                    </h6>
                    <span class="badge bg-light text-dark border">Units</span>
                </div>
                <div class="card-body p-3">
                    <canvas id="stockMovementChart" height="230"></canvas>
                </div>
            </div>
        </div>

        <!-- Vendor Performance & Top 10 Vendors -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-medal text-warning me-2"></i>Vendor Performance &amp; Top Suppliers
                    </h6>
                    <div class="badge bg-success-subtle text-success">
                        On-Time Delivery: {{ $vendorPerformance['on_time_rate'] }}%
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 mb-3">
                        <div class="col-4 text-center p-2 bg-light rounded">
                            <div class="text-muted small">On-Time %</div>
                            <div class="fs-5 fw-bold text-success">{{ $vendorPerformance['on_time_rate'] }}%</div>
                        </div>
                        <div class="col-4 text-center p-2 bg-light rounded">
                            <div class="text-muted small">Late Deliveries</div>
                            <div class="fs-5 fw-bold {{ $vendorPerformance['late_deliveries'] > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ $vendorPerformance['late_deliveries'] }}
                            </div>
                        </div>
                        <div class="col-4 text-center p-2 bg-light rounded">
                            <div class="text-muted small">Quality Rejections</div>
                            <div class="fs-5 fw-bold text-muted">0</div>
                        </div>
                    </div>

                    <h6 class="fw-bold small text-muted text-uppercase mb-2">Top 5 Vendors by Purchase Volume</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" style="font-size: 0.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Vendor</th>
                                    <th class="text-center">POs</th>
                                    <th class="text-end">Total Spend</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topVendors->take(5) as $tv)
                                    <tr>
                                        <td class="fw-semibold text-truncate" style="max-width: 150px;">
                                            <a href="{{ route('vendors.show', $tv) }}" class="text-decoration-none">
                                                {{ $tv->name }}
                                            </a>
                                        </td>
                                        <td class="text-center font-monospace">{{ $tv->po_count ?? 0 }}</td>
                                        <td class="text-end font-monospace fw-bold text-dark">
                                            ₹{{ number_format($tv->total_spend ?? 0, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $tv->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ucfirst($tv->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-2 text-muted">No vendor volume recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FINANCIAL EXPOSURE & RECEIVABLES/PAYABLES SUMMARY -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-danger">
                        <i class="fas fa-hand-holding-usd me-2"></i>Vendor Payables Exposure
                    </h6>
                    <a href="{{ route('vendor-payments.index') }}" class="btn btn-sm btn-link text-decoration-none">Payments &rarr;</a>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="text-muted small">Total Vendor POs</div>
                                <div class="fs-5 fw-bold text-dark font-monospace">₹{{ number_format($arPayable['vendor_total_payable'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="text-muted small">Total Paid to Vendors</div>
                                <div class="fs-5 fw-bold text-success font-monospace">₹{{ number_format($arPayable['vendor_paid'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-danger-subtle rounded text-center">
                                <div class="text-danger small fw-semibold">Net Vendor Outstanding</div>
                                <div class="fs-4 fw-bold text-danger font-monospace">₹{{ number_format($arPayable['vendor_outstanding'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="text-muted small">Overdue Past Due Date</div>
                                <div class="fs-5 fw-bold text-warning font-monospace">₹{{ number_format($arPayable['vendor_overdue'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-success">
                        <i class="fas fa-receipt me-2"></i>Customer Receivables Exposure
                    </h6>
                    <a href="{{ route('receipts.index') }}" class="btn btn-sm btn-link text-decoration-none">Receipts &rarr;</a>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="text-muted small">Total Invoiced to Customers</div>
                                <div class="fs-5 fw-bold text-dark font-monospace">₹{{ number_format($arPayable['customer_total_invoiced'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="text-muted small">Total Collected (Receipts)</div>
                                <div class="fs-5 fw-bold text-success font-monospace">₹{{ number_format($arPayable['customer_received'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-primary-subtle rounded text-center">
                                <div class="text-primary small fw-semibold">Net Customer Receivables</div>
                                <div class="fs-4 fw-bold text-primary font-monospace">₹{{ number_format($arPayable['customer_outstanding'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded text-center">
                                <div class="text-muted small">Overdue Receivables (>30d)</div>
                                <div class="fs-5 fw-bold text-danger font-monospace">₹{{ number_format($arPayable['customer_overdue'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 13. VENDOR-WISE PURCHASE ANALYSIS (DATA TABLE) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-bottom">
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-table me-2 text-primary"></i>Vendor-Wise Purchase &amp; Spend Analysis
                </h5>
                <small class="text-muted">Breakdown of orders, purchase values, payments made, and current vendor balances</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="vendorSearchInput" class="form-control form-control-sm" placeholder="Search vendor..." style="width: 200px;">
                <a href="{{ route('dashboard.export-vendors') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i>Export
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="vendorAnalysisTable">
                    <thead class="table-light">
                        <tr>
                            <th>Vendor Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-center">POs</th>
                            <th class="text-end">Total Spend (₹)</th>
                            <th class="text-end">Paid Amount (₹)</th>
                            <th class="text-end">Outstanding (₹)</th>
                            <th>Last Order</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vendorAnalysis as $v)
                            <tr>
                                <td class="fw-bold">
                                    <a href="{{ route('vendors.show', $v['id']) }}" class="text-decoration-none">
                                        {{ $v['name'] }}
                                    </a>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $v['vendor_type'] }}</span></td>
                                <td>
                                    <span class="badge {{ $v['status'] === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($v['status']) }}
                                    </span>
                                </td>
                                <td class="text-center font-monospace">{{ $v['po_count'] }}</td>
                                <td class="text-end font-monospace fw-bold text-dark">
                                    ₹{{ number_format($v['total_spend'], 2) }}
                                </td>
                                <td class="text-end font-monospace text-success">
                                    ₹{{ number_format($v['total_paid'], 2) }}
                                </td>
                                <td class="text-end font-monospace fw-bold {{ $v['outstanding'] > 0 ? 'text-danger' : 'text-muted' }}">
                                    ₹{{ number_format($v['outstanding'], 2) }}
                                </td>
                                <td class="small">{{ $v['last_po_date'] }}</td>
                                <td class="text-end">
                                    <a href="{{ route('procurements.create', ['vendor_id' => $v['id']]) }}" class="btn btn-sm btn-outline-primary" title="Create Purchase Order">
                                        <i class="fas fa-plus"></i> PO
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    No vendor analysis records available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // 1. PO Status Doughnut Chart
    const poCtx = document.getElementById('poStatusChart');
    if (poCtx) {
        new Chart(poCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($poChart['labels']) !!},
                datasets: [{
                    data: {!! json_encode($poChart['data']) !!},
                    backgroundColor: {!! json_encode($poChart['colors']) !!},
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 10 } }
                    }
                }
            }
        });
    }

    // 2. Vendor Status Doughnut Chart
    const vendorCtx = document.getElementById('vendorStatusChart');
    if (vendorCtx) {
        new Chart(vendorCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($vendorChart['labels']) !!},
                datasets: [{
                    data: {!! json_encode($vendorChart['data']) !!},
                    backgroundColor: {!! json_encode($vendorChart['colors']) !!},
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 10 } }
                    }
                }
            }
        });
    }

    // 3. Monthly Spend Analysis Line/Bar Chart
    const spendCtx = document.getElementById('monthlySpendChart');
    if (spendCtx) {
        new Chart(spendCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthlySpend['labels'] ?? []) !!},
                datasets: [{
                    label: 'Purchase Value (₹)',
                    data: {!! json_encode($monthlySpend['data'] ?? []) !!},
                    backgroundColor: 'rgba(37, 99, 235, 0.75)',
                    borderColor: '#2563eb',
                    borderWidth: 1.5,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) { return '₹' + val.toLocaleString(); }
                        }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // 4. Inventory Movement Trend (Stock In vs Stock Out - 14 Days)
    const stockCtx = document.getElementById('stockMovementChart');
    if (stockCtx) {
        new Chart(stockCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($stockMovement['labels'] ?? []) !!},
                datasets: [
                    {
                        label: 'Stock In (GRN)',
                        data: {!! json_encode($stockMovement['in'] ?? []) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Stock Out (Issue)',
                        data: {!! json_encode($stockMovement['out'] ?? []) !!},
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                }
            }
        });
    }

    // Vendor Table Filter Search
    const searchInput = document.getElementById('vendorSearchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#vendorAnalysisTable tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

});
</script>
@endpush

