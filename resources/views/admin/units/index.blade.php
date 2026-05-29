@extends('layouts.app')

@section('title', 'Unit Management - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-ruler-combined me-2 text-primary"></i>
                                    Unit Management
                                </h4>
                                <p class="mb-0 text-muted">Manage measurement units for products</p>
                            </div>
                            <a href="{{ route('units.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>New Unit
                            </a>
                        </div>
                    </div>

                    <!-- Search & Filter -->
                    <div class="card-body border-bottom">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Search units by name or symbol..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                        Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>
                            @if (request()->hasAny(['search', 'status']))
                                <div class="col-md-2">
                                    <a href="{{ route('units.index') }}" class="btn btn-outline-secondary w-100">
                                        Clear
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>

                    <!-- Units Table -->
                    <div class="card-body p-0">
                        <div class="table-responsive" style="overflow: visible;">
                            <table class="table table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>Unit Name</th>
                                        <th>Symbol</th>
                                        <th>Description</th>
                                        <th>Products</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($units as $index => $unit)
                                        <tr data-unit-id="{{ $unit->id }}">
                                            <td>
                                                <div class="fw-bold text-primary">{{ $units->firstItem() + $index }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $unit->name }}</div>
                                            </td>
                                            <td>
                                                <code class="bg-light px-2 py-1 rounded">{{ $unit->symbol }}</code>
                                            </td>
                                            <td>
                                                <small
                                                    class="text-muted">{{ Str::limit($unit->description, 50) ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info px-2 py-1 rounded-pill">
                                                    {{ $unit->products_count ?? $unit->products()->count() }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusConfig = [
                                                        'active' => [
                                                            'class' => 'success',
                                                            'icon' => 'check-circle',
                                                            'label' => 'Active',
                                                        ],
                                                        'inactive' => [
                                                            'class' => 'danger',
                                                            'icon' => 'ban',
                                                            'label' => 'Inactive',
                                                        ],
                                                    ];
                                                    $config = $statusConfig[$unit->status] ?? $statusConfig['inactive'];
                                                @endphp
                                                <span class="badge bg-{{ $config['class'] }} px-3 py-2 rounded-pill"
                                                    data-unit-id="{{ $unit->id }}"
                                                    data-current-status="{{ $unit->status }}">
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
                                                                href="{{ route('units.show', $unit) }}">
                                                                <i class="fas fa-eye me-2 text-info"></i>View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item py-2"
                                                                href="{{ route('units.edit', $unit) }}">
                                                                <i class="fas fa-edit me-2 text-warning"></i>Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider my-1 mx-2">
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item py-2"
                                                                onclick="toggleStatus({{ $unit->id }}, '{{ $unit->status }}')">
                                                                <i
                                                                    class="fas fa-toggle-{{ $unit->status === 'active' ? 'off' : 'on' }} me-2"></i>
                                                                {{ $unit->status === 'active' ? 'Deactivate' : 'Activate' }}
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider my-1 mx-2">
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item py-2 text-danger"
                                                                onclick="deleteUnit({{ $unit->id }})">
                                                                <i class="fas fa-trash me-2"></i>Delete Unit
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-8">
                                                <i class="fas fa-ruler-combined fa-3x text-muted mb-4 opacity-50"></i>
                                                <h5 class="text-muted mb-3">No units found</h5>
                                                <p class="text-muted mb-4">Try adjusting search/filter or <a
                                                        href="{{ route('units.create') }}"
                                                        class="text-decoration-none">create your first unit</a></p>
                                                <a href="{{ route('units.create') }}" class="btn btn-primary px-4 py-2">
                                                    <i class="fas fa-plus me-2"></i>Create Unit
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if ($units->hasPages())
                        <nav class="mt-4">
                            {{ $units->appends(request()->query())->links('pagination::bootstrap-5') }}
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

                // Delete Unit AJAX
                async function deleteUnit(unitId) {
                    const result = await Swal.fire({
                        title: 'Are you sure?',
                        text: 'This unit will be permanently deleted!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    });

                    if (!result.isConfirmed) return;

                    try {
                        const response = await fetch(`units/${unitId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (data.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: data.message,
                                timer: 2000
                            });
                            window.location.reload();
                        } else {
                            throw new Error(data.message);
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: error.message || 'Something went wrong. Please try again.'
                        });
                    }
                }

                // Toggle Status AJAX
                async function toggleStatus(unitId, currentStatus) {
                    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
                    const action = newStatus === 'active' ? 'activate' : 'deactivate';

                    const result = await Swal.fire({
                        title: `Confirm ${action}?`,
                        text: `Unit status will be changed to ${newStatus}`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: `Yes, ${action} it!`
                    });

                    if (!result.isConfirmed) return;

                    try {
                        const response = await fetch(`units/${unitId}/toggle-status`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
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
