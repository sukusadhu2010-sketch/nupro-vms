@extends('layouts.app')

@section('title', 'Category Details - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold text-dark">
                                    <i class="fas fa-sitemap me-2 text-info"></i>
                                    Category: {{ $category->name }}
                                </h4>
                                <p class="mb-0 text-muted">Product category details</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('product-categories.edit', $category) }}" class="btn btn-outline-warning">
                                    <i class="fas fa-edit me-2"></i>Edit
                                </a>
                                <a href="{{ route('product-categories.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Categories
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3">
                                    <small class="text-muted d-block mb-1">Category Name</small>
                                    <span class="fw-bold fs-5 text-dark">{{ $category->name }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3">
                                    <small class="text-muted d-block mb-1">Status</small>
                                    <span
                                        class="badge bg-{{ $category->status === 'active' ? 'success' : 'danger' }} px-3 py-2">
                                        <i
                                            class="fas fa-{{ $category->status === 'active' ? 'check-circle' : 'ban' }} me-1"></i>{{ ucfirst($category->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3">
                                    <small class="text-muted d-block mb-1">Total Products</small>
                                    <span class="badge bg-info px-3 py-2 fs-6">{{ $category->products->count() }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3">
                                    <small class="text-muted d-block mb-1">Description</small>
                                    <span class="text-dark">{{ $category->description ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Products in this category -->
                        <h5 class="fw-bold mb-3"><i class="fas fa-boxes me-2 text-info"></i>Products in this Category</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product Code</th>
                                        <th>Product Name</th>
                                        <th>HSN Code</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($category->products as $product)
                                        <tr>
                                            <td><code class="bg-light px-2 py-1 rounded">{{ $product->sku }}</code></td>
                                            <td class="fw-semibold">{{ $product->name }}</td>
                                            <td>{{ $product->hsn_code ?? 'N/A' }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $product->status_badge }} px-3 py-2">{{ ucfirst($product->status) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('products.show', $product) }}"
                                                    class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="fas fa-box-open fa-2x mb-3 opacity-50"></i>
                                                <p class="mb-0">No products assigned to this category yet.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
