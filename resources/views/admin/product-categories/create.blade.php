@extends('layouts.app')

@section('title', 'Create Product Category - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-sitemap me-2 text-info"></i>
                                    Create Product Category
                                </h4>
                                <p class="mb-0 text-muted">Categories are used for grouping and auto product codes</p>
                            </div>
                            <a href="{{ route('product-categories.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Categories
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <form method="POST" action="{{ route('product-categories.store') }}" id="categoryCreateForm">
                            @csrf

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <label for="name" class="form-label fw-bold">Category Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control form-control-lg @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                                        placeholder="e.g. Electronics">
                                    <small class="text-muted">Must be unique. Used in the auto-generated product code.</small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label for="status" class="form-label fw-bold">Status <span
                                            class="text-danger">*</span></label>
                                    <select
                                        class="form-select form-select-lg @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label fw-bold">Description</label>
                                    <textarea
                                        class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="4"
                                        placeholder="Short description of this category...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-3 pt-4 border-top mt-4">
                                <a href="{{ route('product-categories.index') }}" class="btn btn-outline-secondary px-5">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-info px-6 fw-semibold" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm d-none me-2" id="spinner"></span>
                                    <i class="fas fa-plus me-2"></i>Add Category
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
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('categoryCreateForm').addEventListener('submit', function() {
                    const submitBtn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('spinner');

                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                });
            });
        </script>
    @endpush
@endsection
