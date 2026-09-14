@extends('layouts.app')

@section('title', 'Create New Product - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-box me-2 text-info"></i>
                                    Create New Product
                                </h4>
                                <p class="mb-0 text-muted">Add new product to inventory</p>
                            </div>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Products
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <form method="POST" action="{{ route('products.store') }}" id="productCreateForm">
                            @csrf

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <label for="name" class="form-label fw-bold">Product Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        class="form-control form-control-lg @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label for="hsn_code" class="form-label fw-bold">HSN Code</label>
                                    <input type="text"
                                        class="form-control form-control-lg @error('hsn_code') is-invalid @enderror"
                                        id="hsn_code" name="hsn_code" value="{{ old('hsn_code') }}"
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
                                                {{ old('product_category_id') == $id ? 'selected' : '' }}>{{ $name }}
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
                                        value="{{ old('sku') }}" readonly tabindex="-1"
                                        placeholder="Select category & enter name...">
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary px-5">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-info px-6 fw-semibold" id="submitBtn">
                                    <span class="spinner-border spinner-border-sm d-none me-2" id="spinner"></span>
                                    <i class="fas fa-plus me-2"></i>Add Product
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
                const form = document.getElementById('productCreateForm');
                const nameInput = document.getElementById('name');
                const categorySelect = document.getElementById('product_category_id');
                const codeInput = document.getElementById('product_code');
                const submitBtn = document.getElementById('submitBtn');
                const spinner = document.getElementById('spinner');

                // Live preview of the auto-generated Product Code (Category + Name)
                function updateCodePreview() {
                    const slug = v => (v || '').toUpperCase().replace(/[^A-Z0-9]+/g, '');
                    const option = categorySelect.options[categorySelect.selectedIndex];
                    const category = slug(option ? option.text : '');
                    const name = slug(nameInput.value);
                    codeInput.value = (category && name) ? (category + '-' + name).substring(0, 50) : '';
                }
                nameInput.addEventListener('input', updateCodePreview);
                categorySelect.addEventListener('change', updateCodePreview);

                // Form submission
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                });
            });
        </script>
        <style>
            .dropzone {
                border: 3px dashed #dee2e6;
                transition: all 0.3s ease;
                cursor: pointer;
                min-height: 200px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }

            .dropzone.highlight {
                border-color: #0d6efd;
                background-color: rgba(13, 110, 253, 0.1);
            }

            .dropzone:hover {
                border-color: #adb5bd;
            }
        </style>
    @endpush
@endsection
