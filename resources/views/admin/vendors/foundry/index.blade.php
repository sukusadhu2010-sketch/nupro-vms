@extends('layouts.app')

@section('title', 'Foundry Management - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-industry me-2 text-info"></i>
                                Foundry Management
                            </h4>
                            <p class="mb-0 text-muted">Manage all registered foundry vendors and their status</p>
                        </div>
                        <a href="{{ route('foundry-vendors.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>New Foundry
                        </a>
                    </div>
                </div>

                <!-- Search & Filter -->
                <div class="card-body border-bottom">
                    <form method="GET" class="row g-3">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search foundries by contact name, particulars or GST No..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>
                                    Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                        </div>
                        @if (request()->hasAny(['search', 'status']))
                        <div class="col-md-2">
                            <a href="{{ route('foundry-vendors.index') }}" class="btn btn-outline-secondary w-100">
                                Clear
                            </a>
                        </div>
                        @endif
                    </form>
                </div>

                <!-- Foundries Table -->
                <div class="card-body p-0">
                    <div class="table-responsive" style="overflow: visible;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Contact Name</th>
                                    <th>Email</th>
                                    <th>Mobile Number</th>
                                    <th>GST No</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($vendors as $index => $vendor)
                                <tr>
                                    <td>
                                        <div class="fw-bold ">{{ $vendors->firstItem() + $index }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $vendor->particulars ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $vendor->email ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <small class="fw-bold text-primary">{{ $vendor->phone ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small class="fw-bold text-primary">{{ $vendor->gst_no ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @php
                                        $statusConfig = [
                                        'active' => [
                                        'class' => 'success',
                                        'icon' => 'check-circle',
                                        'label' => 'Active',
                                        ],
                                        'pending' => [
                                        'class' => 'warning',
                                        'icon' => 'clock',
                                        'label' => 'Pending',
                                        ],
                                        'suspended' => [
                                        'class' => 'danger',
                                        'icon' => 'ban',
                                        'label' => 'Suspended',
                                        ],
                                        ];
                                        $config =
                                        $statusConfig[$vendor->status] ?? $statusConfig['pending'];
                                        @endphp
                                        <span class="badge bg-{{ $config['class'] }} px-3 py-2 rounded-pill"
                                            data-vendor-id="{{ $vendor->id }}"
                                            data-current-status="{{ $vendor->status }}">
                                            <i
                                                class="fas fa-{{ $config['icon'] }} me-1"></i>{{ $config['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown dropstart position-relative">
                                            <button
                                                class="btn btn-sm btn-outline-secondary dropdown-toggle p-2 rounded-circle border-0 shadow-sm hover-shadow"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                title="Actions">
                                                <i class="fas fa-ellipsis-v text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu shadow-lg border-0 py-2"
                                                style="min-width: 160px; z-index: 1100;">
                                                <li>
                                                    <a class="dropdown-item py-2"
                                                        href="{{ route('foundry-vendors.show', $vendor) }}">
                                                        <i class="fas fa-eye me-2 text-info"></i>View Profile
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2"
                                                        href="{{ route('foundry-vendors.edit', $vendor) }}">
                                                        <i class="fas fa-edit me-2 text-warning"></i>Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider my-1 mx-2">
                                                </li>
                                                @if ($vendor->status !== 'pending')
                                                <li>

                                                    <button class="dropdown-item py-2"
                                                        onclick="toggleStatus({{ $vendor->id }}, '{{ $vendor->status }}')">
                                                        <i
                                                            class="fas fa-toggle-{{ $vendor->status === 'active' ? 'off' : 'on' }} me-2"></i>
                                                        {{ $vendor->status === 'active' ? 'Suspend' : 'Activate' }}
                                                    </button>

                                                </li>
                                                @endif
                                                <li>
                                                    <hr class="dropdown-divider my-1 mx-2">
                                                </li>
                                                <li>
                                                    <button class="dropdown-item py-2 text-danger"
                                                        onclick="deleteVendor({{ $vendor->id }})">
                                                        <i class="fas fa-trash me-2"></i>Delete Foundry
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8">
                                        <i class="fas fa-industry fa-3x text-muted mb-4 opacity-50"></i>
                                        <h5 class="text-muted mb-3">No foundries found</h5>
                                        <p class="text-muted mb-4">Try adjusting search/filter or <a
                                                href="{{ route('foundry-vendors.create') }}"
                                                class="text-decoration-none">create your first foundry</a></p>
                                        <a href="{{ route('foundry-vendors.create') }}" class="btn btn-primary px-4 py-2">
                                            <i class="fas fa-plus me-2"></i>Create Foundry
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                @if ($vendors->hasPages())
                <nav class="mt-4">
                    {{ $vendors->appends(request()->query())->links('pagination::bootstrap-5') }}
                </nav>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // CSRF Token
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Delete Vendor AJAX
        async function deleteVendor(vendorId) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: 'This foundry will be permanently deleted!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`foundry-vendors/${vendorId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                });

                if (response.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Foundry has been deleted.',
                        timer: 2000
                    });
                    window.location.reload();
                } else {
                    throw new Error('Delete failed');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Something went wrong. Please try again.'
                });
            }
        }

        // Toggle Status AJAX
        async function toggleStatus(vendorId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'suspended' : 'active';
            const action = newStatus === 'active' ? 'activate' : 'suspend';

            const result = await Swal.fire({
                title: `Confirm ${action}?`,
                text: `Status will be changed to ${newStatus}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${action} it!`
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`foundry-vendors/${vendorId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                });

                if (response.status) {
                    const data = await response.json();

                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Status changed successfully',
                        timer: 2000
                    });
                    window.location.reload();
                } else {
                    throw new Error('Update failed');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Something went wrong. Please refresh and try again.'
                });
            }
        }
    </script>
    @endpush
    @endsection