@extends('layouts.app')

@section('title', 'Edit Enquiry')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('enquiries.index') }}">Enquiries</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </nav>
                <h1 class="h2 mb-1">
                    <i class="fas fa-edit me-2 text-warning"></i>Edit Enquiry #{{ $enquiry->id }}
                </h1>
                <p class="text-muted">Update enquiry details.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('enquiries.update', $enquiry) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row g-4">
                <!-- Customer & Products -->
                <div class="col-lg-8">
                    <div class="card shadow-lg">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Customer & Products</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Customer</label>
                                    <select name="customer_id" class="form-select">
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                {{ $enquiry->customer_id == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->company }} - {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Priority</label>
                                    <select name="priority" class="form-select">
                                        <option value="low" {{ $enquiry->priority == 'low' ? 'selected' : '' }}>Low
                                        </option>
                                        <option value="medium" {{ $enquiry->priority == 'medium' ? 'selected' : '' }}>Medium
                                        </option>
                                        <option value="high" {{ $enquiry->priority == 'high' ? 'selected' : '' }}>High
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div id="dynamicProducts">
                                <label class="form-label fw-bold mb-3">Products</label>
                                <div id="productsContainer">
                                    @foreach ($enquiry->products as $index => $product)
                                        <div class="product-row row mb-3 border p-3 rounded bg-light">
                                            <div class="col-md-5">
                                                <select class="form-select product-select"
                                                    name="products[{{ $index }}][product_id]"
                                                    data-price="{{ $product['estimated_price'] ?? $product['price'] }}">
                                                    @foreach ($products as $p)
                                                        <option value="{{ $p->id }}"
                                                            data-price="{{ $p->price }}"
                                                            data-name="{{ $p->name }}"
                                                            {{ $p->id == $product['product_id'] ? 'selected' : '' }}>
                                                            {{ $p->name }} - ${{ $p->price }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-1.5">
                                                <input type="number" class="form-control qty-input"
                                                    name="products[{{ $index }}][quantity]" min="1"
                                                    value="{{ $product['quantity'] }}">
                                            </div>
                                            <div class="col-md-1.5">
                                                <input type="number" step="0.01" class="form-control est-price"
                                                    name="products[{{ $index }}][estimated_price]"
                                                    value="{{ $product['estimated_price'] ?? '' }}"
                                                    placeholder="Est. Price" min="0">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control notes-input"
                                                    name="products[{{ $index }}][notes]"
                                                    value="{{ $product['notes'] }}">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="subtotal">
                                                    ${{ number_format($product['quantity'] * ($product['estimated_price'] ?? $product['price']), 2) }}
                                                </div>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger mt-1 remove-row">Remove</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" id="addProduct" class="btn btn-outline-success mt-2">
                                    <i class="fas fa-plus"></i> Add Product
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Items: <span
                                        id="totalItems">{{ $enquiry->items->sum('quantity') }}</span></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="h5 fw-bold">Grand Total:</span>
                                <span class="h4 text-success"
                                    id="grandTotal">${{ number_format($enquiry->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mt-4">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Status</h6>
                        </div>
                        <div class="card-body">
                            <select name="status" class="form-select">
                                <option value="pending" {{ $enquiry->status == 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="quoted" {{ $enquiry->status == 'quoted' ? 'selected' : '' }}>Quoted</option>
                                <option value="closed" {{ $enquiry->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                    </div>

                    <div class="card shadow-sm mt-3">
                        <div class="card-body">
                            <textarea name="message" class="form-control" rows="5">{{ $enquiry->message }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-warning btn-lg w-100">
                            <i class="fas fa-save me-2"></i>Update Enquiry
                        </button>
                        <a href="{{ route('enquiries.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            let productIndex = {{ count($enquiry->products) }};
            //  alert(productIndex);
            document.getElementById('addProduct').addEventListener('click', function() {
                productIndex++;
                // alert(productIndex);
                const container = document.getElementById('productsContainer');
                const newRow = document.querySelector('.product-row').cloneNode(true);

                newRow.querySelectorAll('select, input').forEach(el => {
                    if (el.name) {
                        // Use /g to replace ALL instances of [index] in the name
                        // Example: items[0][id] -> items[1][id]
                        el.name = el.name.replace(/\[\d+\]/g, `[${productIndex}]`);
                    }

                    // Clear the value so the new row starts empty
                    el.value = '';
                });
                newRow.querySelector('.remove-row').onclick = function() {
                    this.closest('.product-row').remove();
                    updateTotals();
                };
                newRow.querySelector('.product-select').onchange = handleProductChange;
                newRow.querySelector('.qty-input').oninput = function() {
                    updateRowSubtotal(newRow);
                };
                newRow.querySelector('.est-price').oninput = function() {
                    updateRowSubtotal(newRow);
                };
                container.appendChild(newRow);
                //  productIndex++;
            });

            function handleProductChange(e) {
                const row = e.target.closest('.product-row');
                updateRowSubtotal(row);
            }

            function updateTotals() {
                let totalItems = 0;
                let grandTotal = 0;
                document.querySelectorAll('.product-row').forEach(row => {
                    const qty = parseInt(row.querySelector('.qty-input').value) || 1;
                    totalItems += qty;
                    grandTotal += parseFloat(row.querySelector('.subtotal').dataset.value || 0);
                });
                document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
                document.getElementById('totalItems').textContent = totalItems;
            }

            function updateRowSubtotal(row) {
                const productSelect = row.querySelector('.product-select');
                const estInput = row.querySelector('.est-price');
                const qtyInput = row.querySelector('.qty-input');

                const defaultPrice = parseFloat(productSelect.selectedOptions[0]?.dataset.price || 0);
                const estPrice = parseFloat(estInput.value) || defaultPrice;
                const qty = parseInt(qtyInput.value) || 1;

                const subtotal = (estPrice * qty).toFixed(2);
                const subtotalEl = row.querySelector('.subtotal');
                subtotalEl.textContent = '$' + subtotal;
                subtotalEl.dataset.value = subtotal;
                updateTotals();
            }

            // Init listeners
            document.querySelectorAll('.product-row').forEach(row => {
                row.querySelector('.product-select').onchange = handleProductChange;
                row.querySelector('.qty-input').oninput = () => updateRowSubtotal(row);
                row.querySelector('.est-price').oninput = () => updateRowSubtotal(row);
                row.querySelector('.remove-row').onclick = function() {
                    this.closest('.product-row').remove();
                    updateTotals();
                };
            });
            updateTotals();
        </script>
    @endpush
@endsection
