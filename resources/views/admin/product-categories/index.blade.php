@extends('layouts.app')

@section('title', 'Product Category Management - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-sitemap me-2 text-primary"></i>
                                    Product Category Management
                                </h4>
                                <p class="mb-0 text-muted">Manage categories used for product codes and grouping</p>
                            </div>
                            <a href="{{ route('product-categories.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>New Category
                            </a>
                        </div>
                    </div>

                    <!-- Search & Filter -->
                    <div class="card-body border-bottom">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Search categories by name or description..." value="{{ request('search') }}">
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
                                    <a href="{{ route('product-categories.index') }}" class="btn btn-outline-secondary w-100">
                                        Clear
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>

                    <!-- Categories Table -->
                    <div class="card-body p-0">
                        <div class="table-responsive" style="overflow: visible;">
                            <table class="table table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>Category Name</th>
                                        <th>Description</th>
                                        <th>Products</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($categories as $index => $category)
                                        <tr data-category-id="{{ $category->id }}">
                                            <td>
                                                <div class="fw-bold text-primary">{{ $categories->firstItem() + $index }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $category->name }}</div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ Str::limit($category->description, 60) ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info px-2 py-1 rounded-pill">
                                                    {{ $category->products_count }}
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
                                                    $config = $statusConfig[$category->status] ?? $statusConfig['inactive'];
                                                @endphp
                                                <span class="badge bg-{{ $config['class'] }} px-3 py-2 rounded-pill"
                                                    data-category-id="{{ $category->id }}"
                                                    data-current-status="{{ $category->status }}">
                                                    <i class="fas fa-{{ $config['icon'] }} me-1"></i>{{ $config['label'] }}
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
                                                                href="{{ route('product-categories.show', $category) }}">
                                                                <i class="fas fa-eye me-2 text-info"></i>View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item py-2"
                                                                href="{{ route('product-categories.edit', $category) }}">
                                                                <i class="fas fa-edit me-2 text-warning"></i>Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider my-1 mx-2">
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item py-2"
                                                                onclick="toggleStatus({{ $category->id }}, '{{ $category->status }}')">
                                                                <i
                                                                    class="fas fa-toggle-{{ $category->status === 'active' ? 'off' : 'on' }} me-2"></i>
                                                                {{ $category->status === 'active' ? 'Deactivate' : 'Activate' }}
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider my-1 mx-2">
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item py-2 text-danger"
                                                                onclick="deleteCategory({{ $category->id }})">
                                                                <i class="fas fa-trash me-2"></i>Delete Category
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <i class="fas fa-sitemap fa-3x text-muted mb-4 opacity-50"></i>
                                                <h5 class="text-muted mb-3">No categories found</h5>
                                                <p class="text-muted mb-4">Try adjusting search/filter or <a
                                                        href="{{ route('product-categories.create') }}"
                                                        class="text-decoration-none">create your first category</a></p>
                                                <a href="{{ route('product-categories.create') }}" class="btn btn-primary px-4 py-2">
                                                    <i class="fas fa-plus me-2"></i>Create Category
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if ($categories->hasPages())
                        <nav class="mt-4">
                            {{ $categories->appends(request()->query())->links('pagination::bootstrap-5') }}
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

                // Delete Category AJAX
                async function deleteCategory(categoryId) {
                    const result = await Swal.fire({
                        title: 'Are you sure?',
                        text: 'This category will be permanently deleted!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    });

                    if (!result.isConfirmed) return;

                    try {
                        const response = await fetch(`product-categories/${categoryId}`, {
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
                async function toggleStatus(categoryId, currentStatus) {
                    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
                    const action = newStatus === 'active' ? 'activate' : 'deactivate';

                    const result = await Swal.fire({
                        title: `Confirm ${action}?`,
                        text: `Category status will be changed to ${newStatus}`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: `Yes, ${action} it!`
                    });

                    if (!result.isConfirmed) return;

                    try {
                        const response = await fetch(`product-categories/${categoryId}/toggle-status`, {
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
