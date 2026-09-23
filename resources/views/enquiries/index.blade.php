@extends('layouts.app')

@section('title', 'Enquiry Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-question-circle me-2 text-info"></i>
                            Enquiry Management
                        </h4>
                        <p class="text-muted mb-0">
                            Manage all customer enquiries.
                            @if (isset($enquiries))
                                {{ $enquiries->total() }} total
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Enquiries Table -->
                <div class="card shadow-lg border-0 rounded-3">
                    @if (isset($enquiries))
                        <div class="card-header bg-light border-bottom">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search customer..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                            Pending</option>
                                        <option value="quoted" {{ request('status') == 'quoted' ? 'selected' : '' }}>Quoted
                                        </option>
                                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('enquiries.index') }}"
                                        class="btn btn-outline-secondary w-100">Clear</a>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ route('enquiries.create') }}" class="btn btn-secondary w-100">New
                                        Enquiry</a>
                                </div>
                            </form>
                        </div>
                    @endif
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($enquiries ?? [] as $enquiry)
                                        <tr>
                                            <td>
                                                {{ $enquiry->created_at->format('M d, Y') }}<br>
                                                <small class="text-muted">{{ $enquiry->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-bold">
                                                    {{ $enquiry->customer->company ?? $enquiry->customer->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $enquiry->customer?->user?->email ?? 'No email available' }}</small>
                                                @if ($enquiry->enquiry_number)
                                                    <br><small><code>{{ $enquiry->enquiry_number }}</code></small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $enquiry->items_count }}
                                                    Product{{ $enquiry->items_count !== 1 ? 's' : '' }}</div>
                                                @if ($enquiry->items_count > 0)
                                                    <small
                                                        class="text-muted">{{ Str::limit($enquiry->formatted_items, 60) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $enquiry->priority_badge }} px-3 py-2 fs-6 me-1">
                                                    {{ ucfirst($enquiry->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $enquiry->status_badge }} px-3 py-2 fs-6">
                                                    {{ ucfirst($enquiry->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('enquiries.show', $enquiry) }}"
                                                        class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <!-- <form method="POST"
                                                        action="{{ route('enquiries.update-status', $enquiry) }}"
                                                        class="d-inline" style="display: contents;">
                                                        @csrf @method('POST')
                                                        <select class="form-select form-select-sm status-select"
                                                            name="status" onchange="this.form.submit()">
                                                            <option value="pending"
                                                                {{ $enquiry->status == 'pending' ? 'selected' : '' }}>
                                                                Pending</option>
                                                            <option value="quoted"
                                                                {{ $enquiry->status == 'quoted' ? 'selected' : '' }}>Quoted
                                                            </option>
                                                            <option value="closed"
                                                                {{ $enquiry->status == 'closed' ? 'selected' : '' }}>Closed
                                                            </option>
                                                        </select>
                                                    </form> -->
                                                    <button class="btn btn-outline-danger btn-sm"
                                                        onclick="confirmDelete({{ $enquiry->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <h5>No enquiries yet</h5>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
