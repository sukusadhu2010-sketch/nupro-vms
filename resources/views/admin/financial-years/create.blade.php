@extends('layouts.app')

@section('title', 'Add Financial Year')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4"><i class="fas fa-plus me-2 text-primary"></i>Add Financial Year</h1>

    <form method="POST" action="{{ route('financial-years.store') }}" class="card shadow-sm" style="max-width: 600px;">
        @csrf
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-bold">FY Label <span class="text-danger">*</span></label>
                <input type="text" name="fy_label" class="form-control @error('fy_label') is-invalid @enderror"
                    value="{{ old('fy_label', $suggest['fy_label']) }}" placeholder="e.g. 2026-27" required>
                <small class="text-muted">Auto-suggested from today's date — editable.</small>
                @error('fy_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">FY Short Code <span class="text-danger">*</span></label>
                <input type="text" name="fy_short_code" class="form-control @error('fy_short_code') is-invalid @enderror"
                    value="{{ old('fy_short_code', $suggest['fy_short_code']) }}" placeholder="e.g. 26-27" pattern="\d{2}-\d{2}" required>
                <small class="text-muted">Used in document numbers, e.g. NES/26-27/QTN-00001</small>
                @error('fy_short_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                        value="{{ old('start_date') }}" required>
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">End Date <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                        value="{{ old('end_date') }}" required>
                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="activate" value="1" id="activate" checked>
                <label class="form-check-label" for="activate">Set as active financial year (deactivates the current one)</label>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('financial-years.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
        </div>
    </form>
</div>
@endsection
