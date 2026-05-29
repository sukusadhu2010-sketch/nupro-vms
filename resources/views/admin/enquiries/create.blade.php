@extends('layouts.app')

@section('title', 'Create Enquiry')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('enquiries.index') }}">Enquiries</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
                <h1 class="h2 mb-1">
                    <i class="fas fa-plus-circle me-2 text-success"></i>Create Enquiry
                </h1>
                <p class="text-muted">Create enquiry for customer with multiple products.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('enquiries.store') }}" enctype="multipart/form-data">
            @csrf
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
                                    <label class="form-label fw-bold">Customer <span class="text-danger">*</span></label>
                                    <select name="customer_id" class="form-select" required>
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">
                                                {{ $customer->company }} - {{ $customer->name }} ({{ $customer->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Priority <span class="text-danger">*</span></label>
                                    <select name="priority" class="form-select" required>
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                            </div>
                            <div id="dynamicProducts">
                                <label class="form-label fw-bold mb-3">Products <span class="text-danger">*</span></label>
                                <div id="productsContainer">
                                    <div class="product-row row mb-3 border p-3 rounded bg-light">
                                        <div class="col-md-6">
                                            <select class="form-select product-select" name="products[0][product_id]">
                                                <option value="">Select Product</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                                        data-name="{{ $product->name }}">
                                                        {{ $product->name }} - ${{ $product->price }}
                                                        ({{ $product->vendor->company }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" class="form-control qty-input"
                                                name="products[0][quantity]" min="1" value="1">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" step="0.01" class="form-control est-price"
                                                name="products[0][estimated_price]" placeholder="Est." min="0">
                                        </div>
                                        <div class="col-md-9 mt-3">
                                            <input type="text" class="form-control notes-input" name="products[0][notes]"
                                                placeholder="Notes">
                                        </div>
                                        <div class="col-md-3 d-flex align-items-center justify-content-end mt-3" >
                                            <div class="subtotal">$0.00</div>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger mt-1 remove-row"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="addProduct" class="btn btn-outline-success">
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
                                <span>Total Items: <span id="totalItems">0</span></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="h5 fw-bold">Grand Total:</span>
                                <span class="h4 text-success" id="grandTotal">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mt-4">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Message</h6>
                        </div>
                        <div class="card-body">
                            <textarea name="message" class="form-control" rows="6"
                                placeholder="Additional requirements, timeline, specifications..."></textarea>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-paperclip me-2"></i>Attachments</h6>
                        </div>
                        <div class="card-body">
                            <div class="attachment-dropzone border-dashed border-2 border-primary rounded-3 p-4 text-center mb-3"
                                id="attachmentDropzone">
                                <input type="file" name="attachments[]" class="d-none" id="attachmentInput" multiple
                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <h6 class="mb-2">Click or drag files here</h6>
                                <small class="text-muted">PDF, JPG, PNG, DOC, DOCX (Max 5MB each)</small>
                            </div>
                            <div id="attachmentList" class="list-group list-group-flush"></div>
                            @error('attachments.*')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card shadow-sm mt-3">
                        <div class="card-body text-center py-4">
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-paper-plane me-2"></i>Create Enquiry
                            </button>
                            <a href="{{ route('enquiries.index') }}"
                                class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            let productIndex = 0;

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
                document.getElementById('totalItems').textContent = totalItems;
                document.getElementById('grandTotal').textContent = '$' + grandTotal.toFixed(2);
            }

            document.querySelectorAll('.qty-input, .est-price').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('.product-row');
                    updateRowSubtotal(row);
                });
            });

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

            function updateSubtotal(row, price, qty) {
                updateRowSubtotal(row);
            }

            document.querySelectorAll('.remove-row').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.product-row').remove();
                    updateTotals();
                });
            });

            // Attachment Dropzone
            const dropzone = document.getElementById('attachmentDropzone');
            const fileInput = document.getElementById('attachmentInput');
            const attachmentList = document.getElementById('attachmentList');

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(e => {
                dropzone.addEventListener(e, ev => { ev.preventDefault(); ev.stopPropagation(); });
            });
            ['dragenter', 'dragover'].forEach(e => dropzone.addEventListener(e, () => dropzone.classList.add('bg-primary-subtle')));
            ['dragleave', 'drop'].forEach(e => dropzone.addEventListener(e, () => dropzone.classList.remove('bg-primary-subtle')));

            dropzone.addEventListener('drop', e => { fileInput.files = e.dataTransfer.files; renderFiles(); });
            dropzone.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', renderFiles);

            let fileList = [];

            function renderFiles() {
                fileList = Array.from(fileInput.files);
                attachmentList.innerHTML = fileList.map((file, i) => `
                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file me-2 text-primary"></i>
                            <div>
                                <div class="fw-semibold small">${file.name}</div>
                                <small class="text-muted">${(file.size / 1024).toFixed(1)} KB</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(${i})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `).join('');
            }

            window.removeFile = function(index) {
                const dt = new DataTransfer();
                fileList.forEach((f, i) => { if (i !== index) dt.items.add(f); });
                fileInput.files = dt.files;
                renderFiles();
            };
        </script>
    @endpush
@endsection
