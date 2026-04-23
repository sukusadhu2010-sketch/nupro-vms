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
                        <form method="POST" action="{{ route('products.update', $product) }}" id="productEditForm"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <!-- Product Basics -->
                                <div class="col-lg-8">
                                    <div class="row g-4">
                                        <div class="col-12">
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
                                            <label for="sku" class="form-label fw-bold">SKU <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control form-control-lg @error('sku') is-invalid @enderror"
                                                id="sku" name="sku" value="{{ old('sku', $product->sku) }}"
                                                required>
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
                                                        {{ old('vendor_id', $product->vendor_id) == $id ? 'selected' : '' }}>
                                                        {{ $company }}</option>
                                                @endforeach
                                            </select>
                                            @error('vendor_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <label for="description" class="form-label fw-bold">Description</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                                rows="4">{{ old('description', $product->description) }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-lg-6">
                                            <label for="category" class="form-label fw-bold">Category</label>
                                            <input type="text"
                                                class="form-control form-control-lg @error('category') is-invalid @enderror"
                                                id="category" name="category"
                                                value="{{ old('category', $product->category) }}">
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
                                                <option value="draft"
                                                    {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>
                                                    Draft</option>
                                                <option value="active"
                                                    {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>
                                                    Active</option>
                                                <option value="out_of_stock"
                                                    {{ old('status', $product->status) == 'out_of_stock' ? 'selected' : '' }}>
                                                    Out of Stock</option>
                                                <option value="inactive"
                                                    {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>
                                                    Inactive</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Price & Stock & Current Image -->
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
                                                        value="{{ old('price', $product->price) }}" required>
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
                                                        value="{{ old('stock_quantity', $product->stock_quantity) }}"
                                                        required>
                                                </div>
                                                @error('stock_quantity')
                                                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Image -->
                                            <div class="p-3 bg-light rounded-3">
                                                <h6 class="fw-bold mb-3 text-primary">
                                                    <i class="fas fa-image me-2"></i>Product Image
                                                </h6>
                                                @if ($product->image)
                                                    <div class="current-image mb-4 text-center p-3 border rounded-3">
                                                        <img src="{{ Storage::url($product->image) }}"
                                                            class="img-thumbnail rounded mx-auto d-block mb-2"
                                                            style="max-height: 150px; object-fit: cover;">
                                                        <small
                                                            class="text-muted d-block">{{ basename(Storage::url($product->image)) }}</small>
                                                        <label class="btn btn-sm btn-outline-primary mt-2">
                                                            <i class="fas fa-upload me-1"></i>Replace Image
                                                            <input type="file" name="image" class="d-none"
                                                                accept="image/*">
                                                        </label>
                                                    </div>
                                                @else
                                                    <div class="dropzone border-dashed border-3 border-primary rounded-3 p-4 text-center"
                                                        id="imageDropzone">
                                                        <input type="file" class="d-none" id="image"
                                                            name="image" accept="image/*">
                                                        <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                                        <h6 class="mb-2 text-muted">No image set</h6>
                                                        <small class="text-muted">Upload new image (Max 2MB)</small>
                                                    </div>
                                                @endif
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
