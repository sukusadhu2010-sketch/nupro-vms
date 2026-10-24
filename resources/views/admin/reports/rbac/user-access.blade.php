@extends('layouts.app')

@section('title', 'User Access Report')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-id-card-alt me-2 text-primary"></i>User Access & Permission Report
            </h4>
            <p class="text-muted mb-0">Detailed breakdown of assigned roles and effective permissions for any selected user</p>
        </div>
        @if ($selectedUser)
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print me-1"></i>Print Report
            </button>
        @endif
    </div>

    <!-- Select User -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.rbac.user-access') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Select User to Inspect</label>
                    <select name="user_id" class="form-select select2" onchange="this.form.submit()">
                        <option value="">-- Choose User --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>View Access
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($selectedUser)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">{{ $selectedUser->name }}</h5>
                        <div class="text-muted small">{{ $selectedUser->email }} &bull; Member since {{ $selectedUser->created_at->format('d M Y') }}</div>
                    </div>
                    <div>
                        <span class="fw-semibold me-2">Assigned Roles:</span>
                        @forelse ($selectedUser->roles as $r)
                            <span class="badge bg-primary px-3 py-1 rounded-pill me-1">{{ $r->name }}</span>
                        @empty
                            <span class="badge bg-secondary">No roles assigned</span>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 250px;">Module</th>
                                <th>Department Group</th>
                                <th class="text-center" style="width: 90px;">View</th>
                                <th class="text-center" style="width: 90px;">Create</th>
                                <th class="text-center" style="width: 90px;">Edit</th>
                                <th class="text-center" style="width: 90px;">Delete</th>
                                <th class="text-center" style="width: 90px;">Approve</th>
                                <th class="text-center" style="width: 90px;">Print</th>
                                <th class="text-center" style="width: 90px;">Export</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($modules as $modKey => $info)
                                <tr>
                                    <td class="fw-semibold">
                                        <i class="fas fa-cube me-2 text-primary opacity-50"></i>{{ $info['label'] }}
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $info['group'] }}</span>
                                    </td>
                                    @foreach (['view', 'create', 'edit', 'delete', 'approve', 'print', 'export'] as $act)
                                        <td class="text-center">
                                            @if ($info['actions'][$act] ?? false)
                                                <i class="fas fa-check-circle text-success fs-6" title="Allowed"></i>
                                            @else
                                                <i class="fas fa-minus text-muted opacity-25" title="Denied"></i>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm border-0 py-5 text-center text-muted">
            <i class="fas fa-user-shield fa-3x mb-3 text-secondary opacity-50"></i>
            <h5>Select a user above to inspect their access permissions</h5>
        </div>
    @endif
</div>
@endsection

