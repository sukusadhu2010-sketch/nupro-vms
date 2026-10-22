@extends('layouts.app')

@section('title', 'Permission Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold">
                        <i class="fas fa-key me-2 text-warning"></i>
                        Permission Management
                    </h4>
                    <p class="text-muted mb-0">
                        {{ \App\Models\Permission::count() }} permissions grouped by module —
                        assign them to roles in <a href="{{ route('roles.index') }}" class="text-decoration-underline">Role Management</a>
                    </p>
                </div>
                <a href="{{ route('permissions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>New Permission
                </a>
            </div>

            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="row g-3">
                @forelse ($grouped as $module => $permissions)
                <div class="col-12 col-xl-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-capitalize">
                                <i class="fas fa-folder me-2 text-primary"></i>{{ str_replace('-', ' ', $module) }}
                            </span>
                            <span class="badge bg-primary">{{ count($permissions) }} permissions</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Permission</th>
                                        <th>Key / Slug</th>
                                        <th>Description</th>
                                        <th>Roles</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permissions as $perm)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-capitalize">{{ $perm->module }}</span>
                                            <span class="badge bg-secondary ms-1">{{ ucfirst($perm->action) }}</span>
                                        </td>
                                        <td><code class="small">{{ $perm->name ?? ($perm->module.'.'.$perm->action) }}</code></td>
                                        <td class="small text-muted">{{ $perm->description ?? '—' }}</td>
                                        <td>
                                            @if ($perm->roles->count())
                                                @foreach ($perm->roles as $r)
                                                <span class="badge bg-info me-1 mb-1" title="Role: {{ $r->name }}">{{ $r->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="badge bg-light text-dark">Not assigned</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('permissions.edit', $perm) }}"
                                                class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="deletePermission({{ $perm->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info">No permissions defined yet.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function deletePermission(id) {
        if (!confirm('Delete this permission? Roles using it will lose that access.')) return;
        fetch(`{{ url('permissions') }}/${id}`, {
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