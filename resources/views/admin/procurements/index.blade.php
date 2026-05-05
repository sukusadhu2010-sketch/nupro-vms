@extends('layouts.app')

@section('title', 'Procurements')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-truck me-2 text-danger"></i>
                            Procurement Management
                        </h4>
                        <p class="text-muted mb-0">Manage purchase orders with multiple products</p>
                    </div>
                    <a href="{{ route('procurements.create') }}" class="btn btn-danger">
                        <i class="fas fa-plus me-2"></i>New P.O.
                    </a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Procurement Table -->
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>P.O. #</th>
                                        <th>Sales Order</th>
                                        <th>Vendor</th>
                                        <th>Products</th>
                                        <th>Value</th>
                                        <th>Status</th>
                                        <th>ETA</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($procurements as $procurement)
                                        <tr>
                                            <td>
                                                <span
                                                    class="badge bg-danger px-3 py-2 rounded-pill">{{ $procurement->po_number }}</span>
                                            </td>
                                            <td>
                                                @if ($procurement->salesOrders->count() > 0)
                                                    @foreach ($procurement->salesOrders as $so)
                                                        <div class="fw-bold">{{ $so->order_number }}</div>
                                                        <small class="text-muted">{{ $so->customer->name ?? '' }}</small>
                                                        @if (!$loop->last)
                                                            <hr class="my-1">
                                                        @endif
                                                    @endforeach
                                                @elseif($procurement->salesOrder)
                                                    <div class="fw-bold">{{ $procurement->salesOrder->order_number }}</div>
                                                    <small
                                                        class="text-muted">{{ $procurement->salesOrder->customer->name ?? '' }}</small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $procurement->vendor->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $procurement->vendor->email ?? '' }}</small>
                                            </td>
                                            <td>
                                                <ul class="mb-0 ps-3">
                                                    @foreach ($procurement->items as $item)
                                                        <li>{{ $item->product->name ?? 'N/A' }} ({{ $item->quantity }}
                                                            units)</li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                            <td><strong
                                                    class="text-success">{{ number_format($procurement->total_amount, 2) }}</strong>
                                            </td>
                                            <td>
                                                @if ($procurement->status === 'received')
                                                    <form action="{{ route('procurements.update-status', $procurement) }}"
                                                        method="POST" class="d-inline status-update-form"
                                                        data-po="{{ $procurement->po_number }}">
                                                        @csrf
                                                        <select name="status"
                                                            class="form-select form-select-sm status-select"
                                                            style="min-width: 160px;"
                                                            onchange="if(confirm('Update status for {{ $procurement->po_number }}?')) this.form.submit(); else this.value='{{ $procurement->status }}';">
                                                            <option value="sent" selected>Sent</option>
                                                        </select>
                                                    </form>
                                                @else
                                                    <form action="{{ route('procurements.update-status', $procurement) }}"
                                                        method="POST" class="d-inline status-update-form"
                                                        data-po="{{ $procurement->po_number }}">
                                                        @csrf
                                                        <select name="status"
                                                            class="form-select form-select-sm status-select"
                                                            style="min-width: 160px;"
                                                            onchange="if(confirm('Update status for {{ $procurement->po_number }}?')) this.form.submit(); else this.value='{{ $procurement->status }}';">
                                                            @foreach (['draft' => 'Draft', 'sent' => 'Sent', 'partially_received' => 'Partially Received', 'received' => 'Received', 'cancelled' => 'Cancelled'] as $value => $label)
                                                                <option value="{{ $value }}"
                                                                    {{ $procurement->status == $value ? 'selected' : '' }}>
                                                                    {{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </form>
                                                @endif
                                            </td>
                                            <td>{{ $procurement->expected_delivery_date?->format('Y-m-d') ?? '—' }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('procurements.show', $procurement) }}"
                                                        class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if ($procurement->status !== 'received')
                                                        <a href="{{ route('procurements.edit', $procurement) }}"
                                                            class="btn btn-outline-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    <form action="{{ route('procurements.destroy', $procurement) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this procurement?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">No procurements found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($procurements->hasPages())
                            <div class="card-footer">
                                {{ $procurements->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            @endsection
