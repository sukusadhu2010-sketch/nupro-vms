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
                        <form method="POST" action="{{ route('products.store') }}" id="productCreateForm"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="row g-4">
                                <!-- Product Basics -->
                                <div class="col-lg-8">
                                    <div class="row g-4">
                                        <div class="col-12">
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
                                            <label for="sku" class="form-label fw-bold">SKU <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control form-control-lg @error('sku') is-invalid @enderror"
                                                id="sku" name="sku" value="{{ old('sku') }}" required
                                                maxlength="50">
                                            @error('sku')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6">
                                            <label for="vendor_id" class="form-label fw-bold">Vendor <span
                                                    class="text-danger">*</span></label>
                                            <select
                                                class="form-select form-select-lg @error('vendor_id') is-invalid @enderror"
                                                id="vendor_id" name="vendor_id" required>
                                                <option value="">Select Vendor</option>
                                                @foreach ($vendors as $id => $company)
                                                    <option value="{{ $id }}"
                                                        {{ old('vendor_id') == $id ? 'selected' : '' }}>{{ $company }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('vendor_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <label for="description" class="form-label fw-bold">Description</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                                rows="4" placeholder="Product description...">{{ old('description') }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6">
                                            <label for="category" class="form-label fw-bold">Category</label>
                                            <input type="text"
                                                class="form-control form-control-lg @error('category') is-invalid @enderror"
                                                id="category" name="category" value="{{ old('category') }}">
                                            @error('category')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6">
                                            <label for="status" class="form-label fw-bold">Status <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select form-select-lg @error('status') is-invalid @enderror"
                                                id="status" name="status" required>
                                                <option value="">Select Status</option>
                                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>
                                                    Draft</option>
                                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                                    Active</option>
                                                <option value="out_of_stock"
                                                    {{ old('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock
                                                </option>
                                                <option value="inactive"
                                                    {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Price & Stock & Image -->
                                <div class="col-lg-4">
                                    <div class="card border-0 h-100">
                                        <div class="card-body">
                                            <!-- Price -->
                                            <div class="mb-4 p-3 bg-light rounded-3">
                                                <h6 class="fw-bold mb-3 text-success">
                                                    <i class="fas fa-dollar-sign me-2"></i>Price
                                                </h6>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number"
                                                        class="form-control form-control-lg @error('price') is-invalid @enderror"
                                                        id="price" name="price" step="0.01" min="0"
                                                        value="{{ old('price') }}" required>
                                                </div>
                                                @error('price')
                                                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Stock -->
                                            <div class="mb-4 p-3 bg-light rounded-3">
                                                <h6 class="fw-bold mb-3 text-info">
                                                    <i class="fas fa-warehouse me-2"></i>Inventory
                                                </h6>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                                                    <input type="number"
                                                        class="form-control form-control-lg @error('stock_quantity') is-invalid @enderror"
                                                        id="stock_quantity" name="stock_quantity" min="0"
                                                        value="{{ old('stock_quantity', 0) }}" required>
                                                </div>
                                                @error('stock_quantity')
                                                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Image Upload -->
                                            <div class="p-3 bg-light rounded-3">
                                                <h6 class="fw-bold mb-3 text-primary">
                                                    <i class="fas fa-image me-2"></i>Product Image
                                                </h6>
                                                <div class="dropzone border-dashed border-3 border-primary rounded-3 p-4 text-center"
                                                    id="imageDropzone">
                                                    <input type="file" class="d-none" id="image" name="image"
                                                        accept="image/*">
                                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                                    <h6 class="mb-2">Click or drag image here</h6>
                                                    <small class="text-muted">JPG, PNG (Max 2MB)</small>
                                                </div>
                                                @error('image')
                                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('productCreateForm');
                const dropzone = document.getElementById('imageDropzone');
                const fileInput = document.getElementById('image');
                const submitBtn = document.getElementById('submitBtn');
                const spinner = document.getElementById('spinner');

                // Image upload drag & drop
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropzone.addEventListener(eventName, () => dropzone.classList.add('highlight'), false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, () => dropzone.classList.remove('highlight'), false);
                });

                dropzone.addEventListener('drop', handleDrop, false);

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    fileInput.files = files;
                    handleFiles(files);
                }

                dropzone.addEventListener('click', () => fileInput.click());

                fileInput.addEventListener('change', function(e) {
                    handleFiles(e.target.files);
                });

                function handleFiles(files) {
                    Array.from(files).forEach(previewFile);
                }

                function previewFile(file) {
                    if (!file.type.match('image.*')) return;

                    const reader = new FileReader();
                    reader.onload = e => {
                        dropzone.innerHTML = `
                <img src="${e.target.result}" class="img-thumbnail rounded mx-auto d-block" style="max-height: 200px;">
                <div class="mt-3">
                    <small class="text-success"><i class="fas fa-check me-1"></i>${file.name}</small>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2 remove-image">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
                        document.querySelector('.remove-image').onclick = () => resetDropzone();
                    };
                    reader.readAsDataURL(file);
                }

                function resetDropzone() {
                    dropzone.innerHTML = `
            <input type="file" class="d-none" id="image" name="image" accept="image/*">
            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
            <h6 class="mb-2">Click or drag image here</h6>
            <small class="text-muted">JPG, PNG (Max 2MB)</small>
        `;
                }

                // Form submission
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                });

                // SKU auto-generate suggestion
                document.getElementById('name').addEventListener('input', function() {
                    const sku = document.getElementById('sku');
                    if (!sku.value) {
                        sku.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').substring(0, 8) ||
                            'PROD';
                    }
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
