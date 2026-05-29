@extends('layouts.app')

@section('title', 'Product Management - Admin')

@section('content')
    <div class="glass py-3">
        <div class="row">
            <div class="col-lg-12">
                <div class="card glass">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="fas fa-boxes me-2 text-info"></i>
                                    Product Management
                                </h4>
                                <p class="mb-0 text-muted small">Manage all products across vendors</p>
                            </div>
                            <a href="{{ route('products.create') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-plus me-2"></i>New Product
                            </a>
                        </div>
                    </div>

                    <!-- Search & Filter -->
                    <div class="card-body border-bottom">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Search by name or SKU..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="out_of_stock"
                                        {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                        Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="vendor_id" class="form-select form-select-sm">
                                    <option value="">All Vendors</option>
                                    @foreach ($vendors as $id => $company)
                                        <option value="{{ $id }}"
                                            {{ request('vendor_id') == $id ? 'selected' : '' }}>{{ $company }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-info btn-sm w-100">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Products Table -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Vendor</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Unit</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $index => $product)
                                        <tr data-product-id="{{ $product->id }}">
                                            <td>
                                                <div class="fw-bold text-info">{{ $products->firstItem() + $index }}</div>
                                            </td>
                                            <td>
                                                @if ($product->image)
                                                    <img src="{{ Storage::url($product->image) }}" class="rounded me-2"
                                                        style="width: 50px; height: 50px; object-fit: cover;">
                                                @endif
                                                <div>
                                                    <div class="fw-semibold">{{ $product->name }}</div>
                                                    <small
                                                        class="text-muted">{{ Str::limit($product->description, 60) }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <code class="bg-light px-2 py-1 rounded">{{ $product->sku }}</code>
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $product->vendor->company }}</div>
                                            </td>
<td>
                                                <div class="fw-bold text-success fs-6">
                                                    ₹{{ number_format($product->price, 2) }}</div>
                                            </td>
                                            <td>
                                                @if ($product->stock_quantity > 0)
                                                    <span class="badge bg-success px-3 py-2">
                                                        <i class="fas fa-check me-1"></i>{{ $product->stock_quantity }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger px-3 py-2">
                                                        <i class="fas fa-times me-1"></i>Out of Stock
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($product->unit)
                                                    <span class="badge bg-light text-dark px-2 py-1 rounded-pill">
                                                        {{ $product->unit->symbol }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $statusConfig = [
                                                        'active' => ['class' => 'success', 'icon' => 'check-circle'],
                                                        'draft' => ['class' => 'secondary', 'icon' => 'file'],
                                                        'out_of_stock' => [
                                                            'class' => 'warning',
                                                            'icon' => 'exclamation-triangle',
                                                        ],
                                                        'inactive' => ['class' => 'danger', 'icon' => 'ban'],
                                                    ];
                                                    $config = $statusConfig[$product->status] ?? $statusConfig['draft'];
                                                @endphp
                                                <span class="badge bg-{{ $config['class'] }} px-3 py-2 rounded-pill">
                                                    <i
                                                        class="fas fa-{{ $config['icon'] }} me-1"></i>{{ ucfirst($product->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown dropstart">
                                                    <button
                                                        class="btn btn-sm btn-outline-secondary dropdown-toggle p-2 rounded-circle border-0 shadow-sm"
                                                        type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v text-muted"></i>
                                                    </button>
                                                    <ul class="dropdown-menu shadow-lg border-0 py-2">
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('products.show', $product) }}">
                                                                <i class="fas fa-eye me-2 text-info"></i>View
                                                            </a></li>
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('products.edit', $product) }}">
                                                                <i class="fas fa-edit me-2 text-warning"></i>Edit
                                                            </a></li>
                                                        @if ($product->status !== 'out_of_stock')
                                                            <li>
                                                                <form
                                                                    action="{{ route('products.toggle-status', $product) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="dropdown-item {{ $product->status === 'active' ? 'text-danger' : 'text-success' }}"
                                                                        onclick="return confirm('{{ $product->status === 'active' ? 'Deactivate product?' : 'Activate product?' }}')">
                                                                        <i
                                                                            class="fas fa-toggle-{{ $product->status === 'active' ? 'off' : 'on' }} me-2"></i>
                                                                        {{ $product->status === 'active' ? 'Deactivate' : 'Activate' }}
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('products.destroy', $product) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger"
                                                                    onclick="return confirm('Delete {{ $product->name }}?')">
                                                                    <i class="fas fa-trash me-2"></i>Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-8">
                                                <i class="fas fa-box-open fa-3x text-muted mb-4 opacity-50"></i>
                                                <h5 class="text-muted mb-3">No products found</h5>
                                                <p class="text-muted mb-4">Try adjusting filters or add your first product
                                                </p>
                                                <a href="{{ route('products.create') }}" class="btn btn-info px-4">
                                                    <i class="fas fa-plus me-2"></i>Add Product
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if ($products->hasPages())
                        <div class="card-footer py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small text-muted">
                                    Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of
                                    {{ $products->total() }} products
                                </div>
                                {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
