@extends('layouts.app')

@section('title', 'Edit Product Category - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-edit me-2 text-warning"></i>
                                    Edit Category: {{ $category->name }}
                                </h4>
                                <p class="mb-0 text-muted">Update product category details</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('product-categories.show', $category) }}" class="btn btn-outline-info">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                                <a href="{{ route('product-categories.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Categories
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <form method="POST" action="{{ route('product-categories.update', $category) }}"
                            id="categoryEditForm">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <label for="name" class="form-label fw-bold">Category Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control form-control-lg @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $category->name) }}" required
                                        maxlength="255">
                                    <small class="text-muted">Note: renaming a category will regenerate product codes
                                        only when its products are re-saved.</small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label for="status" class="form-label fw-bold">Status <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select form-select-lg @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                        <option value="active"
                                            {{ old('status', $category->status) == 'active' ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="inactive"
                                            {{ old('status', $category->status) == 'inactive' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label fw-bold">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="4">{{ old('description', $category->description) }}</textarea>
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
                                <button type="submit" class="btn btn-warning px-6 fw-semibold" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm d-none me-2" id="spinner"></span>
                                    <i class="fas fa-save me-2"></i>Update Category
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
                document.getElementById('categoryEditForm').addEventListener('submit', function() {
                    const submitBtn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('spinner');

                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                });
            });
        </script>
    @endpush
@endsection
