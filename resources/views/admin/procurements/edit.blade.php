@extends('layouts.app')

@section('title', 'Edit Procurement ' . $procurement->po_number)

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            @if ($procurement->status === 'received')
                                <i class="fas fa-lock me-2 text-danger"></i>
                            @else
                                <i class="fas fa-truck me-2 text-danger"></i>
                            @endif
                            Edit Procurement {{ $procurement->po_number }}
                        </h4>
                        <p class="text-muted mb-0">
                            @if ($procurement->status === 'received')
                                This procurement is fully received. Only status change to "Sent" is permitted.
                            @else
                                Update purchase order details and log new receipts
                            @endif
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('procurements.show', $procurement) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('procurements.update', $procurement) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light fw-bold">P.O. Details</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">P.O. Number</label>
                                    <input type="text" class="form-control" value="{{ $procurement->po_number }}"
                                        disabled>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Vendor <span class="text-danger">*</span></label>
                                    <select name="vendor_id" class="form-select" required>
                                        <option value="">Select Vendor</option>
                                        @foreach ($vendors as $vendor)
                                            <option value="{{ $vendor->id }}"
                                                {{ old('vendor_id', $procurement->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        @if ($procurement->status === 'received')
                                            <option value="sent">Sent</option>
                                        @else
                                            @foreach (['draft' => 'Draft', 'sent' => 'Sent', 'partially_received' => 'Partially Received', 'received' => 'Received', 'cancelled' => 'Cancelled'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ old('status', $procurement->status) == $value ? 'selected' : '' }}>
                                                    {{ $label }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Order Date</label>
                                    <input type="date" class="form-control"
                                        value="{{ $procurement->order_date?->format('Y-m-d') }}" disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Expected Delivery Date</label>
                                    <input type="date" name="expected_delivery_date" class="form-control"
                                        value="{{ old('expected_delivery_date', $procurement->expected_delivery_date?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Actual Delivery Date</label>
                                    <input type="date" name="actual_delivery_date" class="form-control"
                                        value="{{ old('actual_delivery_date', $procurement->actual_delivery_date?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Linked Sales Orders</label>
                                    <select name="sales_order_ids[]" class="form-select select2" id="salesOrderSelect"
                                        multiple="multiple">
                                        <option value="">Select Sales Orders</option>
                                        @foreach ($salesOrders as $so)
                                            <option value="{{ $so->id }}"
                                                {{ $procurement->salesOrders->contains($so->id) ? 'selected' : '' }}>
                                                {{ $so->order_number }} — {{ $so->customer->name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Shipping Address</label>
                                    <textarea name="shipping_address" class="form-control" rows="2">{{ old('shipping_address', $procurement->shipping_address) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $procurement->notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($procurement->status !== 'received')
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
                                <span>Items &amp; Receipt</span>
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
                                                <th>Ordered</th>
                                                <th>Unit Price</th>
                                                <th>Subtotal</th>
                                                <th>Previously Received</th>
                                                <th>Receive Now</th>
                                                <th>Pending</th>
                                                <th>Receipt Notes</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsTableBody">
                                            @foreach ($procurement->items as $index => $item)
                                                <tr class="item-row" data-item-id="{{ $item->id }}"
                                                    data-quantity="{{ $item->quantity }}"
                                                    data-received="{{ $item->received_quantity }}">
                                                    <input type="hidden" name="items[{{ $index }}][id]"
                                                        value="{{ $item->id }}">
                                                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                                    <td class="fw-bold subtotal-cell">
                                                        ₹{{ number_format($item->subtotal, 2) }}
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge {{ $item->is_fully_received ? 'bg-success' : 'bg-info' }}">
                                                            {{ $item->received_quantity }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            name="items[{{ $index }}][receive_now]"
                                                            class="form-control form-control-sm receive-now-input"
                                                            min="0" max="{{ $item->pending_quantity }}"
                                                            value="{{ old('items.' . $index . '.receive_now', 0) }}"
                                                            data-index="{{ $index }}">
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary pending-badge"
                                                            id="pending-{{ $index }}">{{ $item->pending_quantity }}</span>
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                            name="items[{{ $index }}][receipt_notes]"
                                                            class="form-control form-control-sm"
                                                            placeholder="Optional notes"
                                                            value="{{ old('items.' . $index . '.receipt_notes') }}">
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" name="delete_items[]"
                                                            value="{{ $item->id }}" title="Mark for deletion">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if ($procurement->attachments && count($procurement->attachments))
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-light fw-bold">Existing Attachments</div>
                                <div class="card-body">
                                    @foreach ($procurement->attachments as $attachment)
                                        <a href="{{ Storage::url($attachment) }}"
                                            class="btn btn-outline-primary btn-sm w-100 mb-2" target="_blank">
                                            <i class="fas fa-download me-1"></i>{{ basename($attachment) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-light fw-bold">Add Attachments</div>
                            <div class="card-body">
                                <div class="attachment-dropzone border-dashed border-2 border-primary rounded-3 p-4 text-center mb-3"
                                    id="attachmentDropzone">
                                    <input type="file" name="attachments[]" class="d-none" id="attachmentInput"
                                        multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
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
                    @else
                        <div class="alert alert-danger mb-4">
                            <i class="fas fa-lock me-2"></i>
                            <strong>Locked:</strong> This procurement is fully received. Line items cannot be modified. You
                            may only change the status to "Sent".
                        </div>
                    @endif

                    <div class="d-flex justify-content-end gap-2 mb-4">
                        <a href="{{ route('procurements.show', $procurement) }}"
                            class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-danger">Update Purchase Order</button>
                    </div>
                </form>

                <!-- Receipt History -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light fw-bold">
                        <i class="fas fa-history me-2"></i>Receipt History
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty Received</th>
                                        <th>Date &amp; Time</th>
                                        <th>Received By</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $hasReceipts = false; @endphp
                                    @foreach ($procurement->items as $item)
                                        @foreach ($item->receipts as $receipt)
                                            @php $hasReceipts = true; @endphp
                                            <tr>
                                                <td>{{ $item->product->name ?? 'N/A' }}</td>
                                                <td><span
                                                        class="badge bg-success">{{ $receipt->quantity_received }}</span>
                                                </td>
                                                <td>{{ $receipt->received_at->format('Y-m-d H:i') }}</td>
                                                <td>{{ $receipt->user->name ?? 'N/A' }}</td>
                                                <td>{{ $receipt->notes ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    @if (!$hasReceipts)
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No receipts
                                                recorded yet.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let rowIndex = {{ $procurement->items->count() }};
            const itemsTableBody = document.getElementById('itemsTableBody');
            const products = @json($products);

            // Initialize Select2 for Sales Orders multi-select
            const salesOrderSelect = document.getElementById('salesOrderSelect');
            if (salesOrderSelect) {
                $(salesOrderSelect).select2({
                    placeholder: 'Select sales orders',
                    allowClear: true,
                    width: '100%'
                });
            }

            function createItemRow(data, index) {
                const tr = document.createElement('tr');
                tr.className = 'item-row';
                tr.innerHTML = `
                    <input type="hidden" name="items[${index}][id]" value="">
                    <td>
                        <select name="items[${index}][product_id]" class="form-select product-select" required>
                            <option value="">Select Product</option>
                            ${products.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name} (${p.sku})</option>`).join('')}
                        </select>
                    </td>
                    <td><input type="number" name="items[${index}][quantity]" class="form-control qty-input" min="1" value="${data.quantity || 1}" required></td>
                    <td><input type="number" step="0.01" name="items[${index}][unit_price]" class="form-control price-input" min="0" value="${data.unit_price || 0}" required></td>
                    <td class="subtotal-cell fw-bold">${((data.quantity || 1) * (data.unit_price || 0)).toFixed(2)}</td>
                    <td><span class="badge bg-info">0</span></td>
                    <td><input type="number" name="items[${index}][receive_now]" class="form-control form-control-sm" value="0" min="0"></td>
                    <td><span class="badge bg-secondary pending-badge">0</span></td>
                    <td><input type="text" name="items[${index}][receipt_notes]" class="form-control form-control-sm" placeholder="Optional notes"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-trash"></i></button></td>
                `;
                const select = tr.querySelector('.product-select');
                if (data.product_id) {
                    select.value = data.product_id;
                }
                return tr;
            }

            function attachRowEvents(row) {
                const qtyInput = row.querySelector('.qty-input');
                const priceInput = row.querySelector('.price-input');
                const productSelect = row.querySelector('.product-select');

                if (qtyInput && priceInput) {
                    qtyInput.addEventListener('input', () => updateSubtotal(row));
                    priceInput.addEventListener('input', () => updateSubtotal(row));
                }

                if (productSelect) {
                    productSelect.addEventListener('change', function() {
                        const option = this.options[this.selectedIndex];
                        const price = option.getAttribute('data-price');
                        if (price) {
                            row.querySelector('.price-input').value = price;
                            updateSubtotal(row);
                        }
                    });
                }

                const removeBtn = row.querySelector('.remove-row');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        row.remove();
                    });
                }
            }

            function updateSubtotal(row) {
                const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
                const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
                const cell = row.querySelector('.subtotal-cell');
                if (cell) {
                    cell.textContent = (qty * price).toFixed(2);
                }
            }

            // Add Item button
            const addItemBtn = document.getElementById('addItemRow');
            if (addItemBtn) {
                addItemBtn.addEventListener('click', function() {
                    const row = createItemRow({}, rowIndex);
                    itemsTableBody.appendChild(row);
                    attachRowEvents(row);
                    rowIndex++;
                });
            }

            // Attach events to initial rows
            document.querySelectorAll('.item-row').forEach(row => {
                attachRowEvents(row);
            });

            // Real-time pending calculation
            const inputs = document.querySelectorAll('.receive-now-input');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('tr');
                    const total = parseInt(row.dataset.quantity) || 0;
                    const prev = parseInt(row.dataset.received) || 0;
                    let now = parseInt(this.value) || 0;
                    const maxPending = total - prev;

                    if (now < 0) now = 0;
                    if (now > maxPending) {
                        now = maxPending;
                        this.value = now;
                    }

                    const pending = total - prev - now;
                    const index = this.dataset.index;
                    const badge = document.getElementById('pending-' + index);
                    if (badge) badge.textContent = pending;

                    if (pending === 0) {
                        badge.classList.remove('bg-secondary', 'bg-danger');
                        badge.classList.add('bg-success');
                    } else if (now > 0) {
                        badge.classList.remove('bg-secondary', 'bg-success');
                        badge.classList.add('bg-warning');
                    } else {
                        badge.classList.remove('bg-success', 'bg-warning', 'bg-danger');
                        badge.classList.add('bg-secondary');
                    }
                });
            });

            // Attachment dropzone
            const dropzone = document.getElementById('attachmentDropzone');
            const fileInput = document.getElementById('attachmentInput');
            const attachmentList = document.getElementById('attachmentList');
            let fileList = [];

            if (dropzone && fileInput) {
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
            }

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
