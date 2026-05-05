@extends('layouts.app')

@section('title', 'New Procurement')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="fas fa-truck me-2 text-danger"></i>
                            New Purchase Order
                        </h4>
                        <p class="text-muted mb-0">Create a new procurement manually</p>
                    </div>
                    <a href="{{ route('procurements.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('procurements.store') }}" method="POST" id="procurementForm"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light fw-bold">P.O. Details</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">P.O. Number <span class="text-danger">*</span></label>
                                    <input type="text" name="po_number" class="form-control"
                                        value="{{ old('po_number', 'PO-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4))) }}"
                                        required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Vendor <span class="text-danger">*</span></label>
                                    <select name="vendor_id" class="form-select" required>
                                        <option value="">Select Vendor</option>
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}"
                                                {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Linked Sales Orders (PO Number)</label>
                                    <select name="sales_order_ids[]" class="form-select select2" id="salesOrderSelect"
                                        multiple="multiple">
                                        <option value="">None</option>
                                        @foreach ($salesOrders as $so)
                                            <option value="{{ $so->id }}"
                                                {{ is_array(old('sales_order_ids')) && in_array($so->id, old('sales_order_ids')) ? 'selected' : '' }}>
                                                {{ $so->po_number }} — {{ $so->customer->name ?? '' }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Select one or more to auto-populate items below.</small>
                                    <button type="button" class="btn btn-sm btn-link text-primary d-block mt-1"
                                        id="loadSelectedItems">
                                        <i class="fas fa-download me-1"></i>Load Items from Selected
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Order Date</label>
                                    <input type="date" name="order_date" class="form-control"
                                        value="{{ old('order_date', now()->toDateString()) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Expected Delivery Date</label>
                                    <input type="date" name="expected_delivery_date" class="form-control"
                                        value="{{ old('expected_delivery_date') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select" disabled>
                                        <option value="draft" selected>Draft</option>
                                    </select>
                                    <small class="text-muted">New procurements always start as Draft.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Shipping Address</label>
                                    <textarea name="shipping_address" class="form-control" rows="2">{{ old('shipping_address') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                </div>
                            </div>

                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
                                    <span>Items</span>
                                    <button type="button" class="btn btn-sm btn-success" id="addItemRow">
                                        <i class="fas fa-plus me-1"></i>Add Item
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0" id="itemsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Product <span class="text-danger">*</span></th>
                                                    <th>Qty <span class="text-danger">*</span></th>
                                                    <th>Unit Price <span class="text-danger">*</span></th>
                                                    <th>Subtotal</th>
                                                    <th>Notes</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="itemsTableBody">
                                                <tr class="item-row">
                                                    <td>
                                                        <select name="items[0][product_id]"
                                                            class="form-select product-select" required>
                                                            <option value="">Select Product</option>
                                                            @foreach ($products as $product)
                                                                <option value="{{ $product->id }}"
                                                                    data-price="{{ $product->price }}">
                                                                    {{ $product->name }}
                                                                    ({{ $product->sku }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td><input type="number" name="items[0][quantity]"
                                                            class="form-control qty-input" min="1" value="1"
                                                            required>
                                                    </td>
                                                    <td><input type="number" step="0.01" name="items[0][unit_price]"
                                                            class="form-control price-input" min="0"
                                                            value="0" required>
                                                    </td>
                                                    <td class="subtotal-cell fw-bold">0.00</td>
                                                    <td><input type="text" name="items[0][notes]" class="form-control"
                                                            placeholder="Optional notes"></td>
                                                    <td><button type="button"
                                                            class="btn btn-sm btn-outline-danger remove-row"><i
                                                                class="fas fa-trash"></i></button></td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <th colspan="3" class="text-end">Total:</th>
                                                    <th class="text-success" id="grandTotal">0.00</th>
                                                    <th colspan="2"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <div class="card shadow-sm mb-4">
                                    <div class="card-header bg-light fw-bold">Attachments</div>
                                    <div class="card-body">
                                        <div class="attachment-dropzone border-dashed border-2 border-primary rounded-3 p-4 text-center mb-3"
                                            id="attachmentDropzone">
                                            <input type="file" name="attachments[]" class="d-none"
                                                id="attachmentInput" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                            <h6 class="mb-2">Click or drag files here</h6>
                                            <small class="text-muted">PDF, JPG, PNG, DOC, DOCX (Max 5MB each)</small>
                                        </div>
                                        <div id="attachmentList" class="list-group list-group-flush"></div>
                                        @error('attachments.*')
                                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('procurements.index') }}"
                                            class="btn btn-outline-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-danger">Create Purchase Order</button>
                                    </div>
                </form>
            </div>
        </div>
    @endsection

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let rowIndex = 1;
                const itemsTableBody = document.getElementById('itemsTableBody');
                const salesOrderSelect = document.getElementById('salesOrderSelect');

                // Initialize Select2
                $(salesOrderSelect).select2({
                    placeholder: 'Select sales orders',
                    allowClear: true,
                    width: '100%'
                });

                // Sales order items mapping for auto-population
                const salesOrderItemsMap = @json($salesOrderItemsMap);

                function updateSubtotal(row) {
                    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                    const price = parseFloat(row.querySelector('.price-input').value) || 0;
                    row.querySelector('.subtotal-cell').textContent = (qty * price).toFixed(2);
                    updateGrandTotal();
                }

                function updateGrandTotal() {
                    let total = 0;
                    document.querySelectorAll('.subtotal-cell').forEach(cell => {
                        total += parseFloat(cell.textContent) || 0;
                    });
                    document.getElementById('grandTotal').textContent = total.toFixed(2);
                }

                function attachRowEvents(row) {
                    row.querySelector('.qty-input').addEventListener('input', () => updateSubtotal(row));
                    row.querySelector('.price-input').addEventListener('input', () => updateSubtotal(row));
                    row.querySelector('.product-select').addEventListener('change', function() {
                        const option = this.options[this.selectedIndex];
                        const price = option.getAttribute('data-price');
                        if (price) {
                            row.querySelector('.price-input').value = price;
                            updateSubtotal(row);
                        }
                    });
                    row.querySelector('.remove-row').addEventListener('click', function() {
                        if (document.querySelectorAll('.item-row').length > 1) {
                            row.remove();
                            updateGrandTotal();
                        } else {
                            alert('At least one item is required.');
                        }
                    });
                }

                function createItemRow(data, index) {
                    const tr = document.createElement('tr');
                    tr.className = 'item-row';
                    tr.innerHTML = `
                    <td>
                        <select name="items[${index}][product_id]" class="form-select product-select" required>
                            <option value="">Select Product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="items[${index}][quantity]" class="form-control qty-input" min="1" value="${data.quantity}" required></td>
                    <td><input type="number" step="0.01" name="items[${index}][unit_price]" class="form-control price-input" min="0" value="${data.unit_price}" required></td>
                    <td class="subtotal-cell fw-bold">${(data.quantity * data.unit_price).toFixed(2)}</td>
                    <td><input type="text" name="items[${index}][notes]" class="form-control" placeholder="Optional notes"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-trash"></i></button></td>
                `;
                    // Pre-select the product
                    const select = tr.querySelector('.product-select');
                    select.value = data.product_id;
                    return tr;
                }

                // Attach to initial row
                attachRowEvents(document.querySelector('.item-row'));

                document.getElementById('addItemRow').addEventListener('click', function() {
                    const newRow = document.querySelector('.item-row').cloneNode(true);
                    newRow.querySelectorAll('input, select').forEach(input => {
                        input.name = input.name.replace(/\[\d+\]/, '[' + rowIndex + ']');
                        if (input.tagName === 'SELECT') input.selectedIndex = 0;
                        else if (input.type === 'number') input.value = input.classList.contains(
                            'qty-input') ? 1 : 0;
                        else input.value = '';
                    });
                    newRow.querySelector('.subtotal-cell').textContent = '0.00';
                    itemsTableBody.appendChild(newRow);
                    attachRowEvents(newRow);
                    rowIndex++;
                });

                // Auto-populate items when sales order changes
                salesOrderSelect.addEventListener('change', function() {
                    const soId = this.value;
                    itemsTableBody.innerHTML = '';
                    rowIndex = 0;

                    if (soId && salesOrderItemsMap[soId] && salesOrderItemsMap[soId].length > 0) {
                        salesOrderItemsMap[soId].forEach(item => {
                            const row = createItemRow(item, rowIndex);
                            itemsTableBody.appendChild(row);
                            attachRowEvents(row);
                            rowIndex++;
                        });
                    } else {
                        // Add empty row
                        const row = createItemRow({
                            product_id: '',
                            quantity: 1,
                            unit_price: 0
                        }, 0);
                        itemsTableBody.appendChild(row);
                        attachRowEvents(row);
                        rowIndex = 1;
                    }
                    updateGrandTotal();
                });

                // Load items from multiple selected sales orders
                document.getElementById('loadSelectedItems').addEventListener('click', function() {
                    const selectedOptions = Array.from(salesOrderSelect.selectedOptions);
                    const selectedIds = selectedOptions.map(opt => opt.value).filter(val => val !== '');

                    if (selectedIds.length === 0) {
                        alert('Please select at least one sales order.');
                        return;
                    }

                    itemsTableBody.innerHTML = '';
                    rowIndex = 0;

                    // Collect items from all selected sales orders
                    selectedIds.forEach(soId => {
                        if (salesOrderItemsMap[soId] && salesOrderItemsMap[soId].length > 0) {
                            salesOrderItemsMap[soId].forEach(item => {
                                const row = createItemRow(item, rowIndex);
                                itemsTableBody.appendChild(row);
                                attachRowEvents(row);
                                rowIndex++;
                            });
                        }
                    });

                    if (rowIndex === 0) {
                        // Add empty row if no items found
                        const row = createItemRow({
                            product_id: '',
                            quantity: 1,
                            unit_price: 0
                        }, 0);
                        itemsTableBody.appendChild(row);
                        attachRowEvents(row);
                        rowIndex = 1;
                    }
                    updateGrandTotal();
                });

                // Attachment Dropzone
                const dropzone = document.getElementById('attachmentDropzone');
                const fileInput = document.getElementById('attachmentInput');
                const attachmentList = document.getElementById('attachmentList');
                let fileList = [];

                dropzone.addEventListener('dragover', e => {
                    e.preventDefault();
                    dropzone.classList.add('border-success');
                });
                dropzone.addEventListener('dragleave', () => dropzone.classList.remove('border-success'));
                dropzone.addEventListener('drop', e => {
                    e.preventDefault();
                    dropzone.classList.remove('border-success');
                    fileInput.files = e.dataTransfer.files;
                    renderFiles();
                });
                dropzone.addEventListener('click', () => fileInput.click());
                fileInput.addEventListener('change', renderFiles);

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
                        <button type="button" class="btn btn-sm btn-link text-danger" onclick="removeFile(${i})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `).join('');
                }

                window.removeFile = function(index) {
                    const dt = new DataTransfer();
                    fileList.forEach((f, i) => {
                        if (i !== index) dt.items.add(f);
                    });
                    fileInput.files = dt.files;
                    renderFiles();
                };
            });
        </script>
    @endpush
