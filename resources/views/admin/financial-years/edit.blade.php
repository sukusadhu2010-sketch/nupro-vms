@extends('layouts.app')

@section('title', 'Edit Financial Year')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4"><i class="fas fa-edit me-2 text-primary"></i>Edit FY {{ $fy->fy_label }}</h1>

    @if ($codeLocked)
        <div class="alert alert-warning">
            <i class="fas fa-lock me-1"></i> Documents have already been generated against this FY — the short code is locked
            and cannot be changed (only the label/dates can be corrected).
        </div>
    @endif

    <form method="POST" action="{{ route('financial-years.update', $fy) }}" class="card shadow-sm" style="max-width: 600px;">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-bold">FY Label <span class="text-danger">*</span></label>
                <input type="text" name="fy_label" class="form-control @error('fy_label') is-invalid @enderror"
                    value="{{ old('fy_label', $fy->fy_label) }}" required>
                @error('fy_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">FY Short Code <span class="text-danger">*</span></label>
                <input type="text" name="fy_short_code" class="form-control @error('fy_short_code') is-invalid @enderror"
                    value="{{ old('fy_short_code', $fy->fy_short_code) }}" pattern="\d{2}-\d{2}"
                    {{ $codeLocked ? 'readonly' : 'required' }}>
                @error('fy_short_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                        value="{{ old('start_date', $fy->start_date->format('Y-m-d')) }}" required>
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">End Date <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                        value="{{ old('end_date', $fy->end_date->format('Y-m-d')) }}" required>
                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            @unless ($fy->is_active)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="activate" value="1" id="activate">
                    <label class="form-check-label" for="activate">Set as active financial year</label>
                </div>
            @endunless
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('financial-years.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
        </div>
    </form>
</div>
@endsection
