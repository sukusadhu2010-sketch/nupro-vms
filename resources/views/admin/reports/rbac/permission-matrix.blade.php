@extends('layouts.app')

@section('title', 'RBAC Permission Matrix Report')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-th me-2 text-primary"></i>System Permission Matrix
            </h4>
            <p class="text-muted mb-0">Role vs Permission grid showing all module actions configured per role</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print me-1"></i>Print Matrix
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 75vh; overflow-y: auto;">
                <table class="table table-bordered table-sm mb-0 align-middle text-center">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="text-start" style="min-width: 200px;">Module / Action</th>
                            @foreach ($roles as $role)
                                <th style="min-width: 110px;">
                                    <div class="fw-bold">{{ $role->name }}</div>
                                    <small class="text-muted fw-normal">({{ $role->users()->count() }} users)</small>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissions as $module => $perms)
                            <tr class="table-secondary text-start">
                                <td colspan="{{ $roles->count() + 1 }}" class="fw-bold text-uppercase py-2 ps-3">
                                    <i class="fas fa-folder-open me-2 text-primary"></i>{{ str_replace('-', ' ', $module) }}
                                </td>
                            </tr>
                            @foreach ($perms as $perm)
                                <tr>
                                    <td class="text-start ps-4">
                                        <span class="badge bg-light text-dark border me-1">{{ strtoupper($perm->action) }}</span>
                                        <small class="text-muted">{{ $perm->label ?: ucfirst($perm->action).' '.ucfirst($perm->module) }}</small>
                                    </td>
                                    @foreach ($roles as $role)
                                        @php
                                            $hasPerm = strtolower($role->name) === 'administrator' || $role->hasPermission($perm->module, $perm->action);
                                        @endphp
                                        <td>
                                            @if ($hasPerm)
                                                <i class="fas fa-check-circle text-success fs-6" title="Allowed"></i>
                                            @else
                                                <i class="fas fa-times-circle text-muted fs-6 opacity-25" title="Denied"></i>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

