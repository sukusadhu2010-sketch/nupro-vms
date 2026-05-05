@extends('layouts.app')

@section('title', 'Product Details - Admin')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Product Profile -->
            <div class="col-xl-8">
                <div class="card shadow-lg border-0 rounded-4 h-100">
                    <div class="card-header bg-gradient-info text-white rounded-top-4 border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0 fw-bold">
                                    <i class="fas fa-box me-2"></i>
                                    {{ $product->name }}
                                </h3>
                                <p class="mb-0 opacity-90">Product ID: {{ $product->id }} | SKU: {{ $product->sku }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-edit me-1"></i>Edit Product
                                </a>
                                <a href="{{ route('products.index') }}" class="btn btn-outline-light btn-sm">
                                    <i class="fas fa-list me-1"></i>Products List
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-5">
                        <div class="row g-4">
                            <!-- Product Image & Main Info -->
                            <div class="col-12">
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center mb-4 mb-md-0">
                                        <div class="position-relative">
                                            @if ($product->image)
                                                <img src="{{ Storage::url($product->image) }}"
                                                    class="rounded-4 shadow-lg img-fluid mx-auto d-block"
                                                    style="max-height: 300px; width: 100%; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded-4 d-flex align-items-center justify-content-center"
                                                    style="height: 300px; width: 100%;">
                                                    <i class="fas fa-image fa-5x text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="d-flex justify-content-between align-items-start mb-4">
                                            <div>
                                                <h2 class="mb-2 fw-bold">{{ $product->name }}</h2>
                                                <div class="mb-3">
                                                    @php
                                                        $statusConfig = [
                                                            'active' => [
                                                                'class' => 'success',
                                                                'icon' => 'check-circle',
                                                                'label' => 'Live',
                                                            ],
                                                            'draft' => [
                                                                'class' => 'secondary',
                                                                'icon' => 'file',
                                                                'label' => 'Draft',
                                                            ],
                                                            'out_of_stock' => [
                                                                'class' => 'warning',
                                                                'icon' => 'exclamation-triangle',
                                                                'label' => 'Low Stock',
                                                            ],
                                                            'inactive' => [
                                                                'class' => 'danger',
                                                                'icon' => 'ban',
                                                                'label' => 'Inactive',
                                                            ],
                                                        ];
                                                        $config =
                                                            $statusConfig[$product->status] ?? $statusConfig['draft'];
                                                    @endphp
                                                    <span
                                                        class="badge bg-{{ $config['class'] }} px-4 py-2 rounded-pill fs-6">
                                                        <i
                                                            class="fas fa-{{ $config['icon'] }} me-2"></i>{{ ucfirst($product->status) }}
                                                    </span>
                                                </div>
                                                <p class="text-muted mb-1"><i
                                                        class="fas fa-building me-2 text-info"></i>{{ $product->vendor->company }}
                                                </p>
                                                <p class="mb-1"><i class="fas fa-tag me-2 text-primary"></i>SKU:
                                                    <code>{{ $product->sku }}</code>
                                                </p>
                                                @if ($product->category)
                                                    <p class="mb-0"><i
                                                            class="fas fa-layer-group me-2 text-success"></i>Category:
                                                        {{ $product->category }}</p>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <div class="display-4 fw-bold text-success mb-1">
                                                    ${{ number_format($product->price, 2) }}</div>
                                                <small class="text-muted">Price</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description & Stock -->
                            <div class="col-12">
                                <div class="row g-4">
                                    <div class="col-lg-8">
                                        <div class="card border-0">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0 fw-bold text-info">
                                                    <i class="fas fa-align-left me-2"></i>Description
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                {!! $product->description
                                                    ? nl2br(e($product->description))
                                                    : '<p class="text-muted">No description provided.</p>' !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="row g-3 h-100">
                                            <div class="col-12">
                                                <div class="card border-0 h-100">
                                                    <div class="card-header bg-light">
                                                        <h6 class="mb-0 fw-bold text-warning">
                                                            <i class="fas fa-warehouse me-2"></i>Inventory
                                                        </h6>
                                                    </div>
                                                    <div class="card-body text-center">
                                                        @if ($product->stock_quantity > 0)
                                                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                                            <h3 class="text-success">{{ $product->stock_quantity }} in
                                                                stock</h3>
                                                        @else
                                                            <i
                                                                class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                                            <h3 class="text-warning">Out of stock</h3>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Activity Timeline -->
                            <div class="col-12">
                                <div class="card border-0">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 fw-bold text-secondary">
                                            <i class="fas fa-history me-2"></i>Activity
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-info-subtle rounded-circle p-2 me-3">
                                                        <i class="fas fa-calendar-plus text-info"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">Created</h6>
                                                        <small
                                                            class="text-muted">{{ $product->created_at->format('M d, Y H:i') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-success-subtle rounded-circle p-2 me-3">
                                                        <i class="fas fa-sync text-success"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1">Last Updated</h6>
                                                        <small
                                                            class="text-muted">{{ $product->updated_at->format('M d, Y H:i') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Sidebar -->
            <div class="col-xl-4">
                <div class="card shadow-lg border-0 rounded-4 h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold text-info">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-info">
                                <i class="fas fa-edit me-2"></i>Edit Product Details
                            </a>
                            <form action="{{ route('products.toggle-status', $product) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit"
                                    class="btn {{ $product->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                    onclick="return confirm('{{ $product->status === 'active' ? 'Deactivate this product?' : 'Activate this product?' }}')">
                                    <i class="fas fa-toggle-{{ $product->status === 'active' ? 'off' : 'on' }} me-2"></i>
                                    {{ $product->status === 'active' ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <div class="dropdown-divider my-3"></div>
                            <form action="{{ route('products.destroy', $product) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100"
                                    onclick="return confirm('Delete {{ $product->name }}?')">
                                    <i class="fas fa-trash me-2"></i>Delete Product
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
