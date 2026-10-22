@extends('layouts.app')

@section('title', ($permission->exists ? 'Edit' : 'Create') . ' Permission')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-xl-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-key me-2 text-warning"></i>
                    {{ $permission->exists ? 'Edit Permission' : 'New Permission' }}
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ $permission->exists ? route('permissions.update', $permission) : route('permissions.store') }}"
                    method="POST">
                    @csrf
                    @if ($permission->exists)
                    @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Module <span class="text-danger">*</span></label>
                        <input type="text" name="module" class="form-control" list="moduleList" required
                            maxlength="100" value="{{ old('module', $permission->module ?? '') }}"
                            placeholder="e.g. customers">
                        <datalist id="moduleList">
                            @foreach ($modules as $m)
                            <option value="{{ $m }}"></option>
                            @endforeach
                        </datalist>
                        <small class="text-muted">Group name, e.g. "customers", "purchase-orders"</small>
                        @error('module')
                        <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Action <span class="text-danger">*</span></label>
                        <select name="action" class="form-select" required>
                            @foreach ($actions as $a)
                            <option value="{{ $a }}"
                                {{ old('action', $permission->action ?? '') === $a ? 'selected' : '' }}>
                                {{ ucfirst($a) }}
                            </option>
                            @endforeach
                        </select>
                        @error('action')
                        <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Label</label>
                        <input type="text" name="description" class="form-control" maxlength="255"
                            value="{{ old('description', $permission->description ?? '') }}"
                            placeholder="Optional description">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>{{ $permission->exists ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection