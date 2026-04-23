@extends('layouts.app')

@section('title', 'Product Catalog')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h2 mb-1">
                    <i class="fas fa-boxes-stacked me-2 text-success"></i>
                    Product Catalog
                </h1>
                <p class="text-muted">Browse available products and start your enquiry.</p>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <!-- Filters -->
            <div class="col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0 fw-bold text-success">
                            <i class="fas fa-filter me-2"></i>Filters
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <!-- Search -->
                        <div class="mb-4">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search products...">
                        </div>
                        <!-- Vendor Filter -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-muted mb-2">Vendor</label>
                            <select id="vendorFilter" class="form-select form-select-sm">
                                <option value="">All Vendors</option>
                                @foreach ($products->groupBy('vendor.company') as $company => $items)
                                    <option value="{{ strtolower($company) }}">{{ $company }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Category Filter -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted mb-2">Category</label>
                            <select id="categoryFilter" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                <option value="electronics">Electronics</option>
                                <option value="office">Office</option>
                                <option value="hardware">Hardware</option>
                            </select>
                        </div>
                        <button id="clearFilters" class="btn btn-outline-secondary btn-sm w-100">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <small class="text-muted">Showing {{ $products->firstItem() }} - {{ $products->lastItem() }} of
                        {{ $products->total() }} products</small>
                    <a href="{{ route('customer.enquiries.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>New Enquiry
                    </a>
                </div>
                <div class="row g-4" id="productsGrid">
                    @forelse($products as $product)
                        <div class="col-md-6 col-lg-4 product-card"
                            data-vendor="{{ strtolower($product->vendor->company ?? '') }}"
                            data-category="{{ strtolower($product->category ?? '') }}"
                            data-name="{{ strtolower($product->name) }}">
                            <div class="card shadow-sm h-100 border-0 hover-lift">
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                    style="height: 200px;">
                                    @if ($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded"
                                            alt="{{ $product->name }}" style="max-height: 180px;">
                                    @else
                                        <i class="fas fa-box-open fa-3x text-muted"></i>
                                    @endif
                                </div>
                                <div class="card-body pb-3">
                                    <h6 class="card-title fw-bold">{{ Str::limit($product->name, 40) }}</h6>
                                    <p class="small text-muted mb-2">{{ Str::limit($product->description, 80) }}</p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-light text-dark fs-6 px-2 py-1">
                                            {{ $product->category ?? 'General' }}
                                        </span>
                                        <span
                                            class="fw-bold text-success fs-5">${{ number_format($product->price, 2) }}</span>
                                    </div>
                                    <small class="text-muted d-block mb-3">
                                        <i class="fas fa-store me-1"></i>{{ $product->vendor->company ?? 'N/A' }} |
                                        <i class="fas fa-warehouse me-1"></i>{{ $product->stock_quantity }} in stock
                                    </small>
                                    <a href="{{ route('customer.enquiries.create') }}#add-product-{{ $product->id }}"
                                        class="btn btn-outline-success w-100">
                                        <i class="fas fa-plus me-1"></i>Add to Enquiry
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-boxes-empty fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No products available</h5>
                                <p class="text-muted">Check back later for new products.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const vendorFilter = document.getElementById('vendorFilter');
                const categoryFilter = document.getElementById('categoryFilter');
                const clearFilters = document.getElementById('clearFilters');
                const productCards = document.querySelectorAll('.product-card');

                function filterProducts() {
                    const searchTerm = searchInput.value.toLowerCase();
                    const vendorTerm = vendorFilter.value.toLowerCase();
                    const categoryTerm = categoryFilter.value.toLowerCase();

                    productCards.forEach(card => {
                        const name = card.dataset.name;
                        const vendor = card.dataset.vendor;
                        const category = card.dataset.category;

                        const matchesSearch = name.includes(searchTerm);
                        const matchesVendor = !vendorTerm || vendor.includes(vendorTerm);
                        const matchesCategory = !categoryTerm || category.includes(categoryTerm);

                        card.style.display = (matchesSearch && matchesVendor && matchesCategory) ? '' : 'none';
                    });
                }

                searchInput.addEventListener('input', filterProducts);
                vendorFilter.addEventListener('change', filterProducts);
                categoryFilter.addEventListener('change', filterProducts);
                clearFilters.addEventListener('click', function() {
                    searchInput.value = '';
                    vendorFilter.value = '';
                    categoryFilter.value = '';
                    filterProducts();
                });
            });
        </script>
        <style>
            .hover-lift:hover {
                transform: translateY(-5px);
                transition: all 0.3s ease;
            }
        </style>
    @endpush
@endsection
