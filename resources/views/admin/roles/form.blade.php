@extends('layouts.app')

@section('title', ($role->exists ? 'Edit' : 'Create') . ' Role')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-{{ $role->exists ? 'edit' : 'plus' }} me-2 text-primary"></i>
                        {{ $role->exists ? 'Edit Role' : 'Create New Role' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ $role->exists ? route('roles.update', $role) : route('roles.store') }}"
                        method="POST">
                        @csrf
                        @if ($role->exists)
                        @method('PUT')
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Role Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required maxlength="100"
                                    value="{{ old('name', $role->name ?? '') }}"
                                    placeholder="e.g. Purchase Manager">
                                @error('name')
                                <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Description</label>
                                <input type="text" name="description" class="form-control" maxlength="500"
                                    value="{{ old('description', $role->description ?? '') }}"
                                    placeholder="Short description of the role">
                                @error('description')
                                <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active"
                                        name="is_active" value="1"
                                        {{ old('is_active', $role->exists ? $role->is_active : true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-2">
                            <i class="fas fa-key me-1 text-warning"></i>Permissions
                        </h6>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:180px;">Module</th>
                                        <th>Actions</th>
                                        <th style="width:80px;" class="text-center">All</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($modules as $moduleKey => $module)
                                    <tr>
                                        <td class="fw-semibold">{{ $module['label'] }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-3">
                                                @foreach ($module['actions'] as $action)
                                                @php
                                                $permId = $module['ids'][$action];
                                                $checked = in_array($permId, old('permissions', $role->exists ? $role->permissions->pluck('id')->all() : []));
                                                @endphp
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input perm-cb"
                                                        type="checkbox" name="permissions[]"
                                                        value="{{ $permId }}"
                                                        data-module="{{ $moduleKey }}"
                                                        id="perm_{{ $permId }}"
                                                        {{ $checked ? 'checked' : '' }}>
                                                    <label class="form-check-label small"
                                                        for="perm_{{ $permId }}">{{ ucfirst($action) }}</label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="toggleModule('{{ $moduleKey }}')">Toggle</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>{{ $role->exists ? 'Update Role' : 'Create Role' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleModule(moduleKey) {
        const boxes = document.querySelectorAll(`.perm-cb[data-module="${moduleKey}"]`);
        const allChecked = [...boxes].every(b => b.checked);
        boxes.forEach(b => b.checked = !allChecked);
    }
</script>
@endpush
@endsection