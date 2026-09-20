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
                                    <select name="customer_id" class="form-select select2" data-placeholder="Select Customer">
                                        <option value=""></option>
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
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold small">Product Name <span class="text-danger">*</span></label>
                                                <select class="form-select product-select select2"
                                                    name="products[{{ $index }}][product_id]" required data-placeholder="Select Product">
                                                    <option value=""></option>
                                                    @foreach ($products as $p)
                                                        <option value="{{ $p->id }}"
                                                            data-name="{{ $p->name }}"
                                                            {{ $p->id == $product['product_id'] ? 'selected' : '' }}>
                                                            {{ $p->name }} - {{ $p->productCategory?->name ?? $p->category ?? 'General' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-bold small">Quantity <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control qty-input" inputmode="numeric"
                                                    name="products[{{ $index }}][quantity]"
                                                    value="{{ $product['quantity'] }}" required>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <label class="form-label fw-bold small mb-1">Additional Information</label>
                                                <div class="row g-2">
                                                    <div class="col-6 col-md-3"><label class="form-label fw-bold small mb-1">MOC <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[{{ $index }}][moc]" placeholder="MOC *" value="{{ $product['moc'] ?? '' }}" required></div>
                                                    <div class="col-6 col-md-3"><label class="form-label fw-bold small mb-1">MFG Spec <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[{{ $index }}][mfg_spec]" placeholder="MFG Spec *" value="{{ $product['mfg_spec'] ?? '' }}" required></div>
                                                    <div class="col-6 col-md-3"><label class="form-label fw-bold small mb-1">Trim <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[{{ $index }}][trim]" placeholder="Trim *" value="{{ $product['trim'] ?? '' }}" required></div>
                                                    <div class="col-6 col-md-3"><label class="form-label fw-bold small mb-1">Operation <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[{{ $index }}][operation]" placeholder="Operation *" value="{{ $product['operation'] ?? '' }}" required></div>
                                                    <div class="col-6 col-md-3"><label class="form-label fw-bold small mb-1">End Connection <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[{{ $index }}][end_connection]" placeholder="End Connection *" value="{{ $product['end_connection'] ?? '' }}" required></div>
                                                    <div class="col-6 col-md-3"><label class="form-label fw-bold small mb-1">Rating <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[{{ $index }}][rating]" placeholder="Rating *" value="{{ $product['rating'] ?? '' }}" required></div>
                                                    <div class="col-6 col-md-3"><label class="form-label fw-bold small mb-1">Media <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[{{ $index }}][media]" placeholder="Media *" value="{{ $product['media'] ?? '' }}" required></div>
                                                </div>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <label class="form-label fw-bold small">Remarks</label>
                                                <textarea class="form-control" name="products[{{ $index }}][remarks]" rows="2" placeholder="Remarks">{{ $product['remarks'] ?? $product['notes'] ?? '' }}</textarea>
                                            </div>
                                            <div class="col-12 mt-2 d-flex justify-content-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-trash"></i></button>
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
                                <span>Total Items: <span id="totalItems">{{ $enquiry->items->sum('quantity') }}</span></span>
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

                    <!-- Attachments -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-paperclip me-2"></i>Attachments</h6>
                        </div>
                        <div class="card-body">
                            @if ($enquiry->attachments && count($enquiry->attachments))
                                <div class="mb-3">
                                    <small class="text-muted fw-bold">Existing Files:</small>
                                    <div class="list-group list-group-flush mt-2">
                                        @foreach ($enquiry->attachments as $attachment)
                                            <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <a href="{{ Storage::url($attachment) }}" target="_blank" class="text-decoration-none">
                                                    <i class="fas fa-file me-2 text-primary"></i>
                                                    <span class="small">{{ basename($attachment) }}</span>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            <div class="attachment-dropzone border-dashed border-2 border-primary rounded-3 p-4 text-center mb-3"
                                id="attachmentDropzone">
                                <input type="file" name="attachments[]" class="d-none" id="attachmentInput" multiple
                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <h6 class="mb-2">Add more files</h6>
                                <small class="text-muted">PDF, JPG, PNG, DOC, DOCX (Max 5MB each)</small>
                            </div>
                            <div id="attachmentList" class="list-group list-group-flush"></div>
                            @error('attachments.*')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
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

            document.getElementById('addProduct').addEventListener('click', function() {
                productIndex++;
                const container = document.getElementById('productsContainer');
                const newRow = document.querySelector('.product-row').cloneNode(true);

                newRow.querySelectorAll('select, input, textarea, label').forEach(el => {
                    if (el.name) el.name = el.name.replace(/\[\d+\]/g, `[${productIndex}]`);
                });

                newRow.querySelector('.product-select').value = '';
                newRow.querySelector('.qty-input').value = '1';
                newRow.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
                newRow.querySelectorAll('input[type=text]:not(.qty-input), textarea').forEach(el => el.value = '');

                // Re-init Select2 on the cloned select
                const clonedSelect = newRow.querySelector('.product-select');
                if (window.jQuery && clonedSelect) {
                    jQuery(clonedSelect).select2({ width: '100%', placeholder: 'Select Product', allowClear: true });
                }

                bindRow(newRow);
                container.appendChild(newRow);
            });

            function numericOnly(el) {
                el.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }

            function updateTotals() {
                let totalItems = 0;
                document.querySelectorAll('.product-row').forEach(row => {
                    totalItems += parseInt(row.querySelector('.qty-input').value) || 0;
                });
                document.getElementById('totalItems').textContent = totalItems;
            }

            function bindRow(row) {
                numericOnly(row.querySelector('.qty-input'));

                row.querySelector('.remove-row').onclick = function() {
                    if (document.querySelectorAll('.product-row').length > 1) {
                        this.closest('.product-row').remove();
                        updateTotals();
                    } else {
                        alert('At least one product row is required.');
                    }
                };
            }

            document.querySelectorAll('.product-row').forEach(row => {
                bindRow(row);
            });
            updateTotals();

            // Initialize Select2 on all dropdowns with search
            document.addEventListener('DOMContentLoaded', function() {
                if (window.jQuery) {
                    jQuery('select.select2').each(function() {
                        jQuery(this).select2({
                            width: '100%',
                            placeholder: jQuery(this).data('placeholder') || 'Select',
                            allowClear: true
                        });
                    });
                }
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
