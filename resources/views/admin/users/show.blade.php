@extends('layouts.app')

@section('title', 'User Profile - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <!-- User Profile Card -->
            <div class="col-xl-8">
                <div class="card shadow-lg border-0 rounded-4 h-100">
                    <div class="card-header bg-gradient-primary text-white rounded-top-4 border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0 fw-bold">
                                    <i class="fas fa-user-circle me-2"></i>
                                    User Profile
                                </h3>
                                <p class="mb-0 opacity-90">Detailed view of {{ $user->name }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                <a href="{{ route('users.index') }}" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-list me-1"></i>Users List
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <div class="row g-4">
                            <!-- Main Info -->
                            <div class="col-12">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div
                                            class="avatar avatar-xl bg-gradient-{{ $user->roles->first()?->name === 'admin' ? 'danger' : ($user->roles->first()?->name === 'vendor' ? 'primary' : 'success') }} text-white rounded-circle d-flex align-items-center justify-content-center">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="col">
                                        <h2 class="mb-1 fw-bold">{{ $user->name }}</h2>
                                        <p class="mb-2 text-muted fs-5">{{ $user->email }}</p>
                                        @if ($user->phone)
                                            <p class="mb-0">
                                                <i class="fas fa-phone me-2 text-muted"></i>
                                                {{ $user->phone }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Roles & Permissions -->
                            <div class="col-lg-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="fw-bold text-uppercase text-primary mb-3">
                                            <i class="fas fa-shield-alt me-2"></i>Roles
                                        </h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            @forelse($user->roles as $role)
                                                <span
                                                    class="badge bg-{{ $role->name === 'admin' ? 'danger' : ($role->name === 'vendor' ? 'primary' : 'success') }} px-4 py-2 rounded-pill">
                                                    {{ ucfirst($role->name) }}
                                                </span>
                                            @empty
                                                <span class="badge bg-secondary px-4 py-2 rounded-pill">No Roles</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Activity -->
                            <div class="col-lg-6">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="fw-bold text-uppercase text-primary mb-3">
                                            <i class="fas fa-chart-line me-2"></i>Activity
                                        </h6>
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <div class="fs-5 fw-bold text-primary">
                                                    {{ $user->created_at->diffForHumans() }}</div>
                                                <small class="text-muted">Joined</small>
                                            </div>
                                            <div class="text-end">
                                                <div class="fs-5 fw-bold text-success">
                                                    {{ $user->updated_at->diffForHumans() }}</div>
                                                <small class="text-muted">Last Active</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vendor/Customer Details -->
                            @if ($user->vendor)
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 fw-bold text-primary">
                                                <i class="fas fa-store me-2"></i>Vendor Information
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-4">
                                                <div class="col-md-6">
                                                    <div><strong>Company:</strong> {{ $user->vendor->company }}</div>
                                                    <div><strong>Specialization:</strong>
                                                        {{ $user->vendor->specialization }}</div>
                                                    @if ($user->vendor->address)
                                                        <div><strong>Address:</strong> {{ $user->vendor->address }}</div>
                                                    @endif
                                                </div>
                                                <div class="col-md-6">
                                                    <div><strong>Status:</strong>
                                                        @php $statusConfig = ['active' => 'success', 'pending' => 'warning', 'suspended' => 'danger']; @endphp
                                                        <span
                                                            class="badge bg-{{ $statusConfig[$user->vendor->status] ?? 'secondary' }} px-3 py-1 rounded-pill">
                                                            {{ ucfirst($user->vendor->status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($user->customer)
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 fw-bold text-primary">
                                                <i class="fas fa-user-tie me-2"></i>Customer Information
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-4">
                                                <div class="col-md-6">
                                                    <div><strong>Company:</strong> {{ $user->customer->company }}</div>
                                                    @if ($user->customer->address)
                                                        <div><strong>Address:</strong> {{ $user->customer->address }}</div>
                                                    @endif
                                                </div>
                                                <div class="col-md-6">
                                                    <div><strong>Status:</strong>
                                                        @php $statusConfig = ['active' => 'success', 'pending' => 'warning', 'suspended' => 'danger']; @endphp
                                                        <span
                                                            class="badge bg-{{ $statusConfig[$user->customer->status] ?? 'secondary' }} px-3 py-1 rounded-pill">
                                                            {{ ucfirst($user->customer->status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Sidebar -->
            <div class="col-xl-4">
                <div class="card shadow-lg border-0 rounded-4 h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                            @if ($user->vendor && $user->vendor->status !== 'pending')
                                <a href="{{ route('vendors.edit', $user->vendor) }}" class="btn btn-outline-info">
                                    <i class="fas fa-store me-2"></i>Edit Vendor Details
                                </a>
                            @endif
                            @if ($user->customer)
                                <a href="#" class="btn btn-outline-warning">
                                    <i class="fas fa-user-tie me-2"></i>Manage Customer
                                </a>
                            @endif
                            <div class="dropdown-divider my-3"></div>
                            <button class="btn btn-danger" onclick="deleteUser({{ $user->id }})">
                                <i class="fas fa-trash me-2"></i>Delete User
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            async function deleteUser(userId) {
                const result = await Swal.fire({
                    title: 'Delete User?',
                    text: 'This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete!'
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
                        Swal.fire('Deleted!', 'User removed successfully.', 'success').then(() => {
                            window.location.href = "{{ route('users.index') }}";
                        });
                    }
                } catch (error) {
                    Swal.fire('Error!', 'Failed to delete user.', 'error');
                }
            }
        </script>
    @endpush
@endsection
