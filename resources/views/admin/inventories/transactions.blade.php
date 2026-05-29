@extends('layouts.app')

@section('title', 'Inventory Transactions')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-history me-2"></i>Inventory Transactions
            </h2>
            <div>
                <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-boxes me-2"></i>Inventory
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('inventories.transactions') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control"
                            value="{{ $startDate ?? now()->startOfMonth()->toDateString() }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control"
                            value="{{ $endDate ?? now()->toDateString() }}">
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

        <!-- Summary -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Inflow</h5>
                        <h2 class="mb-0">+{{ $rangeSummary['total_inflow'] }}</h2>
                        <small>Units received</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Outflow</h5>
                        <h2 class="mb-0">-{{ $rangeSummary['total_outflow'] }}</h2>
                        <small>Units dispatched</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Date Range</h5>
                        <h6 class="mb-0">{{ $rangeSummary['start_date'] }} to {{ $rangeSummary['end_date'] }}</h6>
                        <small>{{ $transactions->count() }} transactions</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions by Date -->
        @forelse($groupedTransactions as $date => $dayTransactions)
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar me-2"></i>{{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                        </h5>
                        <div>
                            <span
                                class="badge bg-success me-2">+{{ $dayTransactions->where('transaction_type', 'inflow')->sum('quantity') }}</span>
                            <span
                                class="badge bg-danger">-{{ $dayTransactions->where('transaction_type', 'outflow')->sum('quantity') }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
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
                                @foreach ($dayTransactions as $transaction)
                                    <tr>
                                        <td>
                                            <a href="{{ route('inventories.show', $transaction->inventory) }}">
                                                {{ $transaction->product->name ?? 'N/A' }}
                                            </a>
                                        </td>
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
                                        <td class="text-end">{{ $transaction->previous_quantity }}</td>
                                        <td class="text-end">{{ $transaction->new_quantity }}</td>
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
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-white py-5">
                    <i class="fas fa-inbox fs-1 mb-3"></i>
                    <h5>No transactions found</h5>
                    <p>There are no inventory transactions for the selected date range.</p>
                </div>
            </div>
        @endforelse
    </div>

    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    @endpush
@endsection
