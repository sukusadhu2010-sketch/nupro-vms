@extends('layouts.app')

@section('title', 'New Enquiry')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('customer.products.index') }}" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Catalog
                            </a>
                        </li>
                        <li class="breadcrumb-item active">New Enquiry</li>
                    </ol>
                </nav>
                <h1 class="h2 mb-1">
                    <i class="fas fa-file-alt me-2 text-primary"></i>
                    Create New Enquiry
                </h1>
                <p class="text-muted">Select multiple products, add details and submit your enquiry.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('customer.enquiries.store') }}" id="enquiryForm"
            enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                <!-- Product Selection -->
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="fas fa-list-ul me-2"></i>Selected Products
                                <span id="productCount" class="badge bg-primary ms-2">0</span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div id="productsTableContainer">
                                <div class="empty-state p-5 text-center bg-light rounded-3 m-3">
                                    <i class="fas fa-plus-circle fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No products selected</h6>
                                    <p class="text-muted mb-0">Browse products from catalog and click "Add to Enquiry"</p>
                                    <a href="{{ route('customer.products.index') }}" class="btn btn-outline-primary">
                                        Browse Products
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="productsTable" style="display: none;">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-center">Price</th>
                                            <th>Notes</th>
                                            <th class="text-center">Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="productsTbody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary & Message -->
                <div class="col-lg-4">
                    <!-- Total Summary -->
                    <div class="card shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-calculator me-2"></i>Summary
                            </h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Items:</span>
                                <span id="totalItems">0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold h5">Total Amount:</span>
                                <span class="fw-bold h4 text-success" id="grandTotal">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-comment-dots me-2"></i>Additional Message
                            </h6>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" id="message" name="message" rows="5"
                                placeholder="Describe your requirements, delivery timeline, any special instructions..."></textarea>
                            @error('message')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-paperclip me-2"></i>Attachments
                            </h6>
                        </div>
                        <div class="card-body">
                            <input type="file" class="form-control" id="attachments" name="attachments[]" multiple
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" />
                            <small class="text-muted">Max 5MB per file. PDF, images, docs supported.</small>
                            @error('attachments')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm" id="submitBtn" disabled>
                            <i class="fas fa-paper-plane me-2"></i>Submit Enquiry
                        </button>
                        <a href="{{ route('customer.enquiries.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Hidden inputs for form data -->
            <input type="hidden" name="products" id="productsData">
        </form>
    </div>

    @push('scripts')
        <script>
            let selectedProducts = [];

            function updateTable() {
                const tbody = document.getElementById('productsTbody');
                const table = document.getElementById('productsTable');
                const container = document.getElementById('productsTableContainer');
                const productCount = document.getElementById('productCount');
                const totalItems = document.getElementById('totalItems');
                const grandTotal = document.getElementById('grandTotal');
                const submitBtn = document.getElementById('submitBtn');

                if (selectedProducts.length === 0) {
                    table.style.display = 'none';
                    container.querySelector('.empty-state').style.display = 'block';
                    productCount.textContent = '0';
                    totalItems.textContent = '0';
                    grandTotal.textContent = '$0.00';
                    submitBtn.disabled = true;
                    return;
                }

                container.querySelector('.empty-state').style.display = 'none';
                table.style.display = 'table';
                productCount.textContent = selectedProducts.length;

                let totalItemsCount = 0;
                let grandTotalAmount = 0;

                tbody.innerHTML = selectedProducts.map((product, index) => `
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <div class="bg-light rounded-circle p-2 me-3">
                        <i class="fas fa-box fa-lg text-muted"></i>
                    </div>
                    <div>
                        <strong>${product.name}</strong>
                        <br><small class="text-muted">SKU: ${product.sku} | Vendor: ${product.vendor}</small>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <input type="number" class="form-control qty-input" min="1" value="${product.quantity}" data-index="${index}">
            </td>
            <td class="text-center fw-bold text-success">$${product.price.toFixed(2)}</td>
            <td>
                <input type="text" class="form-control notes-input" value="${product.notes}" data-index="${index}" placeholder="Optional notes">
            </td>
            <td class="text-center fw-bold text-success" id="subtotal-${index}">$${(product.price * product.quantity).toFixed(2)}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-danger remove-product" data-index="${index}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');

                selectedProducts.forEach((product, index) => {
                    totalItemsCount += product.quantity;
                    grandTotalAmount += product.price * product.quantity;
                });

                totalItems.textContent = totalItemsCount;
                grandTotal.textContent = '$' + grandTotalAmount.toFixed(2);
                submitBtn.disabled = false;

                // Event listeners for qty/notes changes
                document.querySelectorAll('.qty-input, .notes-input').forEach(input => {
                    input.addEventListener('input', function() {
                        const idx = parseInt(this.dataset.index);
                        selectedProducts[idx].quantity = parseInt(document.querySelector(
                            `[data-index="${idx}"].qty-input`).value) || 1;
                        selectedProducts[idx].notes = document.querySelector(
                            `[data-index="${idx}"].notes-input`).value;
                        updateTotals(idx);
                    });
                });

                // Remove buttons
                document.querySelectorAll('.remove-product').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idx = parseInt(this.dataset.index);
                        selectedProducts.splice(idx, 1);
                        updateTable();
                    });
                });
            }

            function updateTotals(index) {
                const product = selectedProducts[index];
                const subtotalEl = document.getElementById(`subtotal-${index}`);
                subtotalEl.textContent = '$' + (product.price * product.quantity).toFixed(2);
                updateTable(); // Recalc grand total
            }

            // Form submit
            document.getElementById('enquiryForm').addEventListener('submit', function(e) {
                document.getElementById('productsData').value = JSON.stringify(selectedProducts);
            });

            // Pre-populate from URL hash (add-product-ID)
            const hash = window.location.hash;
            if (hash.startsWith('#add-product-')) {
                const productId = hash.replace('#add-product-', '');
                // Simulate adding product (in real app, AJAX fetch product)
                // For demo, add placeholder
                selectedProducts.push({
                    id: productId,
                    name: 'Sample Product',
                    sku: 'SKU123',
                    vendor: 'Sample Vendor',
                    price: 99.99,
                    quantity: 1,
                    notes: ''
                });
                updateTable();
            }
        </script>
    @endpush

    <style>
        .empty-state {
            border: 2px dashed #dee2e6;
        }

        .qty-input,
        .notes-input {
            width: 80px;
        }

        .sticky-top {
            position: sticky;
        }

        #grandTotal {
            font-size: 2rem;
        }

        @media (max-width: 768px) {
            .qty-input {
                width: 60px;
            }
        }
    </style>
@endsection
