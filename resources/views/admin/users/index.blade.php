@extends('layouts.app')

@section('title', 'User Management - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-users me-2 text-primary"></i>
                                    User Management
                                </h4>
                                <p class="mb-0 text-muted">Manage all users, roles and permissions</p>
                            </div>

                        </div>
                    </div>

                    <!-- Search & Filter -->
                    <div class="card-body border-bottom">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Search users by name or email..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <!-- <select name="role" class="form-select">
                                    <option value="">All Roles</option>
                                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="vendor" {{ request('role') == 'vendor' ? 'selected' : '' }}>Vendor
                                    </option>
                                    <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>Customer
                                    </option>
                                </select> -->
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                            </div>

                            <div class="col-md-2">
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-100">
                                    Clear
                                </a>
                            </div>

                            <div class="col-md-2">
                                <a href="{{ route('users.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>New User
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Users Table -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $index => $user)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-primary">{{ $users->firstItem() + $index }}</div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-gradient-{{ $user->roles->first()?->name === 'admin' ? 'danger' : ($user->roles->first()?->name === 'vendor' ? 'primary' : 'success') }} text-white rounded-circle me-3 d-flex align-items-center justify-content-center"
                                                        style="width: 40px; height: 40px;">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $user->name }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </td>
                                            <td>
                                                @foreach ($user->roles as $role)
                                                    <span
                                                        class="badge bg-{{ $role->name === 'admin' ? 'danger' : ($role->name === 'vendor' ? 'primary' : 'success') }} px-3 py-1 rounded-pill">
                                                        {{ ucfirst($role->name) }}
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td>
                                                @if ($user->vendor)
                                                    <span class="badge bg-info px-2 py-1 rounded-pill">
                                                        <i class="fas fa-store me-1"></i>Vendor
                                                    </span>
                                                @elseif($user->customer)
                                                    <span class="badge bg-warning px-2 py-1 rounded-pill">
                                                        <i class="fas fa-user-tie me-1"></i>Customer
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary px-2 py-1 rounded-pill">Basic</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($user->vendor)
                                                    @php $status = $user->vendor->status; @endphp
                                                @elseif($user->customer)
                                                    @php $status = $user->customer->status; @endphp
                                                @else
                                                    <span class="badge bg-secondary px-2 py-1 rounded-pill">Active</span>
                                                @endif
                                                @if (isset($status))
                                                    @php
                                                        $statusConfig = [
                                                            'active' => [
                                                                'class' => 'success',
                                                                'icon' => 'check-circle',
                                                            ],
                                                            'pending' => ['class' => 'warning', 'icon' => 'clock'],
                                                            'suspended' => ['class' => 'danger', 'icon' => 'ban'],
                                                        ];
                                                        $config = $statusConfig[$status] ?? $statusConfig['pending'];
                                                    @endphp
                                                    <span class="badge bg-{{ $config['class'] }} px-3 py-2 rounded-pill">
                                                        <i
                                                            class="fas fa-{{ $config['icon'] }} me-1"></i>{{ ucfirst($status) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $user->updated_at?->diffForHumans() ?? 'Never' }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="dropdown dropstart">
                                                    <button
                                                        class="btn btn-sm btn-outline-secondary dropdown-toggle p-2 rounded-circle border-0 shadow-sm"
                                                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                        title="Actions">
                                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                                    </button>
                                                    <ul class="dropdown-menu shadow-lg border-0 py-2"
                                                        style="min-width: 160px;">
                                                        <li>
                                                            <a class="dropdown-item py-2"
                                                                href="{{ route('users.show', $user) }}">
                                                                <i class="fas fa-eye me-2 text-info"></i>View Profile
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item py-2"
                                                                href="{{ route('users.edit', $user) }}">
                                                                <i class="fas fa-edit me-2 text-warning"></i>Edit User
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider my-1 mx-2">
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item py-2 text-danger w-100 text-start"
                                                                onclick="deleteUser({{ $user->id }})">
                                                                <i class="fas fa-trash me-2"></i>Delete User
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-8">
                                                <i class="fas fa-user-slash fa-3x text-muted mb-4 opacity-50"></i>
                                                <h5 class="text-muted mb-3">No users found</h5>
                                                <p class="text-muted mb-4">Try adjusting your search or create your first
                                                    user</p>
                                                <a href="{{ route('users.create') }}" class="btn btn-primary px-4">
                                                    <i class="fas fa-plus me-2"></i>Create User
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if ($users->hasPages())
                        <div class="card-footer bg-white border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of
                                    {{ $users->total() }} users
                                </div>
                                {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Delete User AJAX
            async function deleteUser(userId) {
                const result = await Swal.fire({
                    title: 'Are you sure?',
                    text: "This user and all associated data will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                });

                if (!result.isConfirmed) return;

                try {
                    const response = await fetch(`/admin/users/${userId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json',
                        }
                    });

                    if (response.ok) {
                        document.querySelector(`tr[data-user-id="${userId}"]`)?.remove();
                        Swal.fire('Deleted!', 'User has been deleted.', 'success');
                    } else {
                        throw new Error('Delete failed');
                    }
                } catch (error) {
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                }
            }
        </script>
    @endpush
@endsection
