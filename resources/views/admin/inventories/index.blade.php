@extends('layouts.app')

@section('title', 'Inventory Management')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-boxes me-2"></i>Inventory Management
            </h2>
            <div>
                <a href="{{ route('inventories.dashboard') }}" class="btn btn-outline-primary">
                    <i class="fas fa-chart-line me-2"></i>Dashboard
                </a>
                <a href="{{ route('inventories.transactions') }}" class="btn btn-outline-info">
                    <i class="fas fa-list me-2"></i>Transactions
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('inventories.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Transaction Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="inflow" {{ $type === 'inflow' ? 'selected' : '' }}>Inflow</option>
                            <option value="outflow" {{ $type === 'outflow' ? 'selected' : '' }}>Outflow</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Products</h5>
                        <h2 class="mb-0">{{ $inventoryValue['product_count'] ?? 0 }}</h2>
                        <small>In inventory</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Quantity</h5>
                        <h2 class="mb-0">{{ $inventoryValue['total_quantity'] ?? 0 }}</h2>
                        <small>Units in stock</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Value</h5>
                        <h2 class="mb-0">₹{{ number_format($inventoryValue['total_value'] ?? 0, 2) }}</h2>
                        <small>Inventory value</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">Low Stock Alerts</h5>
                        <h2 class="mb-0">{{ $lowStockProducts->count() ?? 0 }}</h2>
                        <small>Need reorder</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Summary -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-day me-2"></i>Today's Summary ({{ $summary['date'] }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6 class="text-muted">Inflow</h6>
                                    <h3 class="text-success">+{{ $summary['inflow'] }}</h3>
                                    <small>Units received</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6 class="text-muted">Outflow</h6>
                                    <h3 class="text-danger">-{{ $summary['outflow'] }}</h3>
                                    <small>Units dispatched</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6 class="text-muted">Net Change</h6>
                                    <h3 class="{{ $summary['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $summary['net'] >= 0 ? '+' : '' }}{{ $summary['net'] }}
                                    </h3>
                                    <small>Net today</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Current Stock Levels
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="inventoryTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Reserved</th>
                                <th class="text-end">Available</th>
                                <th class="text-end">Reorder Level</th>
                                <th>Status</th>
                                <th>Last Transaction</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventories as $inventory)
                                <tr>
                                    <td>{{ $inventory->product->name ?? 'N/A' }}</td>
                                    <td>{{ $inventory->product->sku ?? '' }}</td>
                                    <td class="text-end">{{ $inventory->quantity }}</td>
                                    <td class="text-end">{{ $inventory->reserved_quantity }}</td>
                                    <td class="text-end">
                                        <strong>{{ $inventory->available_quantity }}</strong>
                                    </td>
                                    <td class="text-end">{{ $inventory->reorder_level }}</td>
                                    <td>
                                        @if ($inventory->needs_reorder)
                                            <span class="badge bg-danger">Low Stock</span>
                                        @else
                                            <span class="badge bg-success">In Stock</span>
                                        @endif
                                    </td>
                                    <td>{{ $inventory->last_transaction_date?->format('d M Y') ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('inventories.show', $inventory) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">
                                        No inventory records found. Stock will appear here when procurements are received.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Recent Transactions
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm" id="transactionsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Prev</th>
                                <th class="text-end">New</th>
                                <th>Reference</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions->take(10) as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
                                    <td>{{ $transaction->product->name ?? 'N/A' }}</td>
                                    <td>
                                        @if ($transaction->transaction_type === 'inflow')
                                            <span class="badge bg-success">
                                                <i class="fas fa-arrow-down me-1"></i>Inflow
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-arrow-up me-1"></i>Outflow
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $transaction->quantity }}</td>
                                    <td class="text-end">{{ $transaction->previous_quantity }}</td>
                                    <td class="text-end">{{ $transaction->new_quantity }}</td>
                                    <td>
                                        @if ($transaction->reference_url)
                                            <a href="{{ $transaction->reference_url }}">
                                                {{ $transaction->reference_label }}
                                            </a>
                                        @else
                                            {{ $transaction->notes ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td>{{ $transaction->user->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        No transactions found for the selected date range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($transactions->count() > 10)
                    <div class="text-center mt-3">
                        <a href="{{ route('inventories.transactions') }}" class="btn btn-outline-primary">
                            View All Transactions
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    @endpush

    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#inventoryTable').DataTable({
                    ordering: true,
                    paging: true,
                    info: true,
                    language: {
                        emptyTable: "No inventory data available"
                    }
                });

                $('#transactionsTable').DataTable({
                    ordering: true,
                    paging: true,
                    info: false,
                    language: {
                        emptyTable: "No transactions found"
                    }
                });
            });
        </script>
    @endpush
@endsection
