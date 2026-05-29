@extends('layouts.app')

@section('title', 'Inventory Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-chart-line me-2"></i>Inventory Dashboard
            </h2>
            <div>
                <a href="{{ route('inventories.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-boxes me-2"></i>Inventory List
                </a>
                <a href="{{ route('inventories.transactions') }}" class="btn btn-outline-info">
                    <i class="fas fa-list me-2"></i>Transactions
                </a>
            </div>
        </div>

        <!-- Today Summary -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-day me-2"></i>Today's Summary ({{ $todaySummary['date'] }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="p-3">
                                    <h6 class="text-white">Received (Inflow)</h6>
                                    <h3 class="text-success mb-0">+{{ $todaySummary['inflow'] }}</h3>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3">
                                    <h6 class="text-white">Dispatched (Outflow)</h6>
                                    <h3 class="text-danger mb-0">-{{ $todaySummary['outflow'] }}</h3>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3">
                                    <h6 class="text-white">Net Change</h6>
                                    <h3 class="{{ $todaySummary['net'] >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                        {{ $todaySummary['net'] >= 0 ? '+' : '' }}{{ $todaySummary['net'] }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Products</h5>
                        <h2 class="mb-0">{{ $inventoryValue['product_count'] }}</h2>
                        <small>In inventory system</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Quantity</h5>
                        <h2 class="mb-0">{{ $inventoryValue['total_quantity'] }}</h2>
                        <small>Total units</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Inventory Value</h5>
                        <h2 class="mb-0">₹{{ number_format($inventoryValue['total_value'], 2) }}</h2>
                        <small>Total value</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">Low Stock Alerts</h5>
                        <h2 class="mb-0">{{ $lowStockProducts->count() }}</h2>
                        <small>Need reorder</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        @if ($lowStockProducts->count() > 0)
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alerts - Reorder Required!
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th class="text-end">Current Stock</th>
                                    <th class="text-end">Reorder Level</th>
                                    <th class="text-end">Shortage</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lowStockProducts as $inventory)
                                    <tr>
                                        <td>{{ $inventory->product->name }}</td>
                                        <td>{{ $inventory->product->sku }}</td>
                                        <td class="text-end">
                                            <span class="text-danger">{{ $inventory->quantity }}</span>
                                        </td>
                                        <td class="text-end">{{ $inventory->reorder_level }}</td>
                                        <td class="text-end">
                                            {{ $inventory->reorder_level - $inventory->quantity }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('inventories.show', $inventory) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Recent Transactions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Recent Transactions (Last 7 Days)
                </h5>
            </div>
            <div class="card-body">
                @if ($recentTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th class="text-end">Qty</th>
                                    <th>Reference</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentTransactions->take(20) as $transaction)
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
                                        <td class="text-end">
                                            <strong
                                                class="{{ $transaction->transaction_type === 'inflow' ? 'text-success' : 'text-danger' }}">
                                                {{ $transaction->transaction_type === 'inflow' ? '+' : '-' }}{{ $transaction->quantity }}
                                            </strong>
                                        </td>
                                        <td>
                                            @if ($transaction->reference_url)
                                                <a href="{{ $transaction->reference_url }}">
                                                    {{ $transaction->reference_label }}
                                                </a>
                                            @else
                                                {{ $transaction->notes ?? '-' }}
                                            @endif
                                        </td>
                                        <td>{{ $transaction->user->name ?? 'System' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-white py-4">
                        <i class="fas fa-inbox fs-1 mb-2"></i>
                        <p>No recent transactions. Stock updates will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
