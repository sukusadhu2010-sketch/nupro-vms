@extends('layouts.app')

@section('title', 'Inventory - ' . ($inventory->product->name ?? 'Details'))

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-boxes me-2"></i>Inventory Details
            </h2>
            <div>
                <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Inventory
                </a>
            </div>
        </div>

        <!-- Product Info -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-white">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>{{ $inventory->product->name ?? 'N/A' }}</h4>
                                <p class="text-white mb-1">
                                    <strong>SKU:</strong> {{ $inventory->product->sku ?? 'N/A' }}
                                </p>
                                <p class="text-white mb-1">
                                    <strong>Category:</strong> {{ $inventory->product->category ?? 'N/A' }}
                                </p>
                                <p class="text-white mb-0">
                                    <strong>Unit Price:</strong> ₹{{ number_format($inventory->product->price ?? 0, 2) }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="card bg-light text-white">
                                            <div class="card-body">
                                                <h4 class="mb-0">{{ $inventory->quantity }}</h4>
                                                <small class="text-white">Total Qty</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-warning text-white">
                                            <div class="card-body">
                                                <h4 class="mb-0">{{ $inventory->reserved_quantity }}</h4>
                                                <small class="text-white">Reserved</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <h4 class="mb-0">{{ $inventory->available_quantity }}</h4>
                                                <small class="text-white">Available</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Status -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card {{ $inventory->needs_reorder ? 'border-danger' : 'border-success' }}">
                    <div class="card-body text-center">
                        <h5 class="card-title">Stock Status</h5>
                        @if ($inventory->needs_reorder)
                            <span class="badge bg-danger fs-5">
                                <i class="fas fa-exclamation-triangle me-2"></i>Low Stock - Reorder Needed!
                            </span>
                        @else
                            <span class="badge bg-success fs-5">
                                <i class="fas fa-check-circle me-2"></i>In Stock
                            </span>
                        @endif
                        <p class="mt-2 mb-0">
                            <small class="text-white">Reorder Level: {{ $inventory->reorder_level }}</small>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center text-white">
                        <h5 class="card-title">Last Transaction</h5>
                        <h4>{{ $inventory->last_transaction_date?->format('d M Y') ?? 'N/A' }}</h4>
                        <small class="text-white">{{ $inventory->last_transaction_date?->diffForHumans() ?? '' }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center text-white">
                        <h5 class="card-title">Inventory Value</h5>
                        <h4>₹{{ number_format($inventory->quantity * ($inventory->product->price ?? 0), 2) }}</h4>
                        <small class="text-white">{{ $inventory->quantity }} x
                            ₹{{ number_format($inventory->product->price ?? 0, 2) }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Transaction History
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="transactionsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Previous</th>
                                <th class="text-end">New</th>
                                <th>Reference</th>
                                <th>Notes</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
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
                                        <span
                                            class="{{ $transaction->transaction_type === 'inflow' ? 'text-success' : 'text-danger' }}">
                                            {{ $transaction->transaction_type === 'inflow' ? '+' : '-' }}{{ $transaction->quantity }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ $transaction->previous_quantity }}</td>
                                    <td class="text-end">{{ $transaction->new_quantity }}</td>
                                    <td>
                                        @if ($transaction->reference_url)
                                            <a href="{{ $transaction->reference_url }}" target="_blank">
                                                {{ $transaction->reference_label }}
                                            </a>
                                        @else
                                            <span class="text-white">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $transaction->notes ?? '-' }}</small>
                                    </td>
                                    <td>{{ $transaction->user->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-white">
                                        No transactions found for this product.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#transactionsTable').DataTable({
                    ordering: true,
                    paging: true,
                    info: true,
                    language: {
                        emptyTable: "No transactions found"
                    }
                });
            });
        </script>
    @endpush
@endsection
