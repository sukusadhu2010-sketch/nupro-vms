@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold">
                        <i class="fas fa-user-shield me-2 text-primary"></i>
                        Role Management
                    </h4>
                    <p class="text-muted mb-0">Create, edit and manage system roles</p>
                </div>
                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>New Role
                </a>
            </div>

            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Role Name</th>
                                    <th>Description</th>
                                    <th>Users</th>
                                    <th>Permissions</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $role->name }}</div>
                                    </td>
                                    <td class="small text-muted">{{ $role->description ?? '—' }}</td>
                                    <td>{{ $role->users_count ?? $role->users()->count() }}</td>
                                    <td>{{ $role->permissions()->count() }}</td>
                                    <td>
                                        <span class="badge {{ $role->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $role->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('roles.edit', $role) }}"
                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                            class="btn btn-sm {{ $role->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            onclick="toggleStatus({{ $role->id }})"
                                            title="{{ $role->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $role->is_active ? 'eye-slash' : 'eye' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteRole({{ $role->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No roles found.</td>
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

@push('scripts')
<script>
    function toggleStatus(id) {
        fetch(`{{ url('roles') }}/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(d => d.status ? location.reload() : alert(d.message || 'Failed'));
    }

    function deleteRole(id) {
        if (!confirm('Delete this role? This cannot be undone.')) return;
        fetch(`{{ url('roles') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(d => d.status ? location.reload() : alert(d.message || 'Failed'));
    }
</script>
@endpush
@endsection