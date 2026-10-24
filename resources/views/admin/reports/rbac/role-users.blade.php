@extends('layouts.app')

@section('title', 'Role Wise Users Report')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-users-cog me-2 text-primary"></i>Role Wise Users Report
            </h4>
            <p class="text-muted mb-0">Overview of all system roles, active members, and associated permissions</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print me-1"></i>Print Report
            </button>
        </div>
    </div>

    <div class="row g-4">
        @forelse ($roles as $role)
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">{{ $role->name }}</h5>
                            <small class="text-muted">{{ $role->description ?? 'No description' }}</small>
                        </div>
                        <span class="badge {{ $role->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $role->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between small text-muted mb-3 pb-2 border-bottom">
                            <span><i class="fas fa-key me-1 text-warning"></i>{{ $role->permissions_count }} Permissions</span>
                            <span><i class="fas fa-users me-1 text-info"></i>{{ $role->users->count() }} Users</span>
                        </div>
                        @if ($role->users->count() > 0)
                            <div class="list-group list-group-flush" style="max-height: 220px; overflow-y: auto;">
                                @foreach ($role->users as $u)
                                    <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-0">
                                        <div>
                                            <div class="fw-semibold small">{{ $u->name }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $u->email }}</div>
                                        </div>
                                        <span class="badge bg-light text-dark border">ID: {{ $u->id }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-muted small">
                                <i class="fas fa-user-slash fa-2x mb-2 text-secondary opacity-50 d-block"></i>
                                No users currently assigned to this role.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                No roles defined.
            </div>
        @endforelse
    </div>
</div>
@endsection

