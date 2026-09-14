@extends('layouts.app')

@section('title', 'Edit Product - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-edit me-2 text-warning"></i>
                                    Edit Product: {{ $product->name }}
                                </h4>
                                <p class="mb-0 text-muted">Update product details and inventory</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-outline-info">
                                    <i class="fas fa-eye me-2"></i>View Product
                                </a>
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Products
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <form method="POST" action="{{ route('products.update', $product) }}" id="productEditForm">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <label for="name" class="form-label fw-bold">Product Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control form-control-lg @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $product->name) }}"
                                        required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label for="hsn_code" class="form-label fw-bold">HSN Code</label>
                                    <input type="text"
                                        class="form-control form-control-lg @error('hsn_code') is-invalid @enderror"
                                        id="hsn_code" name="hsn_code" value="{{ old('hsn_code', $product->hsn_code) }}"
                                        placeholder="e.g. 8471">
                                    <small class="text-muted">Duplicate HSN codes are allowed.</small>
                                    @error('hsn_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label for="product_category_id" class="form-label fw-bold">Product Category
                                        <span class="text-danger">*</span></label>
                                    <select
                                        class="form-select form-select-lg @error('product_category_id') is-invalid @enderror"
                                        id="product_category_id" name="product_category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $id => $name)
                                            <option value="{{ $id }}"
                                                {{ old('product_category_id', $product->product_category_id) == $id ? 'selected' : '' }}>{{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label for="product_code" class="form-label fw-bold">Product Code
                                        <span class="badge bg-secondary ms-1">Auto-generated</span></label>
                                    <input type="text" class="form-control form-control-lg bg-light" id="product_code"
                                        value="{{ old('sku', $product->sku) }}" readonly tabindex="-1">
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary px-5">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-warning px-6 position-relative" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm d-none me-2" id="spinner"></span>
                                    <i class="fas fa-save me-2"></i>Update Product
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
                // Form submission
                document.getElementById('productEditForm').addEventListener('submit', function() {
                    const submitBtn = document.getElementById('submitBtn');
                    const spinner = document.getElementById('spinner');

                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                });
            });
        </script>
    @endpush
@endsection
