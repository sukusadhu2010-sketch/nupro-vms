@extends('layouts.app')

@section('title', 'Create Quotation')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('enquiries.index') }}">Enquiries</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('enquiries.show', $enquiry) }}">#{{ $enquiry->id }}</a>
                        </li>
                        <li class="breadcrumb-item active">Create Quotation</li>
                    </ol>
                </nav>
                <h1 class="h2 mb-1">
                    <i class="fas fa-file-invoice-dollar me-2 text-success"></i>Create Quotation
                </h1>
                <p class="text-muted">Generate quotation for Enquiry #{{ $enquiry->id }} — Version {{ $nextVersion }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('quotations.store', $enquiry) }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-lg">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Quotation Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Quote Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="quote_number" class="form-control"
                                        value="{{ $quoteNumber }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Version <span class="text-danger">*</span></label>
                                    <input type="number" name="version" class="form-control" value="{{ $nextVersion }}"
                                        readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Valid Until</label>
                                    <input type="date" name="valid_until" class="form-control"
                                        value="{{ now()->addDays(30)->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Customer</label>
                                    <input type="text" class="form-control"
                                        value="{{ $enquiry->customer->company ?? $enquiry->customer->name }}" disabled>
                                </div>
                            </div>

                            <label class="form-label fw-bold mb-3">Quote Items <span class="text-danger">*</span></label>
                            <div id="itemsContainer">
                                @foreach ($enquiry->items as $index => $item)
                                    <div class="item-row row mb-3 border p-3 rounded bg-light">
                                        <div class="col-md-5">
                                            <select class="form-select product-select"
                                                name="items[{{ $index }}][product_id]">
                                                @foreach (App\Models\Product::with('vendor')->where('status', 'active')->get() as $p)
                                                    <option value="{{ $p->id }}" data-price="{{ $p->price }}"
                                                        {{ $p->id == $item->product_id ? 'selected' : '' }}>
                                                        {{ $p->name }} - ${{ $p->price }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="form-control qty-input"
                                                name="items[{{ $index }}][quantity]" min="1"
                                                value="{{ $item->quantity }}">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" step="0.01" class="form-control unit-price"
                                                name="items[{{ $index }}][unit_price]"
                                                value="{{ $item->estimated_price ?? $item->product->price }}"
                                                min="0">
                                        </div>
                                        <div class="col-md-2">
                                            <div class="subtotal">
                                                ${{ number_format($item->quantity * ($item->estimated_price ?? $item->product->price), 2) }}
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <input type="text" class="form-control"
                                                name="items[{{ $index }}][notes]" placeholder="Item notes"
                                                value="{{ $item->notes }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="addItem" class="btn btn-outline-success">
                                <i class="fas fa-plus"></i> Add Item
                            </button>

                            <div class="mt-4">
                                <label class="form-label fw-bold">Quotation Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Terms, delivery, payment conditions..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Items:</span>
                                <span id="totalItems">{{ $enquiry->items->sum('quantity') }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="h5 fw-bold">Grand Total:</span>
                                <span class="h4 text-success"
                                    id="grandTotal">${{ number_format($enquiry->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div class="card shadow-sm mt-3">
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
                                <i class="fas fa-save me-2"></i>Generate Quotation
                            </button>
                            <a href="{{ route('enquiries.show', $enquiry) }}"
                                class="btn btn-outline-secondary w-100 mt-2">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            let itemIndex = {{ count($enquiry->items) }};

            document.getElementById('addItem').addEventListener('click', function() {
                const container = document.getElementById('itemsContainer');
                const newRow = document.querySelector('.item-row').cloneNode(true);
                newRow.querySelectorAll('select, input').forEach(el => {
                    el.name = el.name.replace(/\[\d+\]/, `[${itemIndex}]`);
                    if (el.type !== 'button') el.value = el.tagName === 'SELECT' ? el.value : '';
                });
                newRow.querySelector('.subtotal').textContent = '$0.00';
                newRow.querySelector('.remove-row').onclick = function() {
                    newRow.remove();
                    updateTotals();
                };
                newRow.querySelector('.product-select').onchange = function() {
                    updateRow(newRow);
                };
                newRow.querySelector('.qty-input').oninput = function() {
                    updateRow(newRow);
                };
                newRow.querySelector('.unit-price').oninput = function() {
                    updateRow(newRow);
                };
                container.appendChild(newRow);
                itemIndex++;
            });

            function updateRow(row) {
                const price = parseFloat(row.querySelector('.unit-price').value) || 0;
                const qty = parseInt(row.querySelector('.qty-input').value) || 1;
                row.querySelector('.subtotal').textContent = '$' + (price * qty).toFixed(2);
                updateTotals();
            }

            function updateTotals() {
                let totalItems = 0;
                let grandTotal = 0;
                document.querySelectorAll('.item-row').forEach(row => {
                    const qty = parseInt(row.querySelector('.qty-input').value) || 0;
                    const price = parseFloat(row.querySelector('.unit-price').value) || 0;
                    totalItems += qty;
                    grandTotal += qty * price;
                });
                document.getElementById('totalItems').textContent = totalItems;
                document.getElementById('grandTotal').textContent = '$' + grandTotal.toFixed(2);
            }

            document.querySelectorAll('.item-row').forEach(row => {
                row.querySelector('.product-select').onchange = function() {
                    const price = this.selectedOptions[0].dataset.price;
                    row.querySelector('.unit-price').value = price;
                    updateRow(row);
                };
                row.querySelector('.qty-input').oninput = function() {
                    updateRow(row);
                };
                row.querySelector('.unit-price').oninput = function() {
                    updateRow(row);
                };
                row.querySelector('.remove-row').onclick = function() {
                    row.remove();
                    updateTotals();
                };
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
