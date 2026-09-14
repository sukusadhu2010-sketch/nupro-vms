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
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small">Product Name <span class="text-danger">*</span></label>
                                            <select class="form-select product-select" name="products[0][product_id]" required>
                                                <option value="">Select Product</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                                        data-name="{{ $product->name }}">
                                                        {{ $product->name }} - ₹{{ number_format($product->price, 2) }}
                                                        ({{ $product->vendor?->company ?? 'No Vendor' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold small">Quantity <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control qty-input" inputmode="numeric"
                                                name="products[0][quantity]" value="1" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Unit Price</label>
                                            <input type="text" class="form-control unit-price-input" inputmode="decimal"
                                                name="products[0][unit_price]" placeholder="0.00">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Total Price</label>
                                            <input type="text" class="form-control total-price-input" inputmode="decimal"
                                                name="products[0][total_price]" placeholder="0.00" readonly>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label class="form-label fw-bold small mb-1">LC / Credit / Advance / PIC / PDC / Proforma Invoice</label>
                                            <div class="d-flex flex-wrap gap-3">
                                                @foreach (['LC' => 'LC', 'Credit' => 'Credit', 'Advance' => 'Advance', 'PIC' => 'PIC', 'PDC' => 'PDC', 'Proforma Invoice' => 'Proforma Invoice'] as $key => $label)
                                                    <div class="form-check">
                                                        <input class="form-check-input pay-method" type="checkbox"
                                                            name="products[0][payment_methods][]" value="{{ $key }}" id="pay_0_{{ $loop->index }}">
                                                        <label class="form-check-label small" for="pay_0_{{ $loop->index }}">{{ $label }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label class="form-label fw-bold small mb-1">Description</label>
                                            <div class="row g-2">
                                                <div class="col-md-3"><label class="form-label fw-bold small mb-1">MOC <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[0][moc]" placeholder="MOC *" required></div>
                                                <div class="col-md-3"><label class="form-label fw-bold small mb-1">MFG Spec <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[0][mfg_spec]" placeholder="MFG Spec *" required></div>
                                                <div class="col-md-2"><label class="form-label fw-bold small mb-1">Trim <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[0][trim]" placeholder="Trim *" required></div>
                                                <div class="col-md-2"><label class="form-label fw-bold small mb-1">Operation <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[0][operation]" placeholder="Operation *" required></div>
                                                <div class="col-md-2"><label class="form-label fw-bold small mb-1">End Connection <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[0][end_connection]" placeholder="End Connection *" required></div>
                                                <div class="col-md-3"><label class="form-label fw-bold small mb-1">Rating <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[0][rating]" placeholder="Rating *" required></div>
                                                <div class="col-md-3"><label class="form-label fw-bold small mb-1">Media <span class="text-danger">*</span></label><input type="text" class="form-control form-control-sm" name="products[0][media]" placeholder="Media *" required></div>
                                            </div>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label class="form-label fw-bold small">Remarks</label>
                                            <textarea class="form-control" name="products[0][remarks]" rows="2" placeholder="Remarks"></textarea>
                                        </div>
                                        <div class="col-12 mt-2 d-flex justify-content-end">
                                            <div class="subtotal me-3 fw-bold">₹0.00</div>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-trash"></i></button>
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
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Amount (Qty):</span>
                                <span id="totalAmount">₹0.00</span>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold small mb-1">Tax Details</label>
                                <div class="form-check">
                                    <input class="form-check-input tax-type" type="radio" name="tax_type" value="igst" checked id="taxIgst">
                                    <label class="form-check-label small" for="taxIgst">IGST @18%</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input tax-type" type="radio" name="tax_type" value="sgst_cgst" id="taxSgst">
                                    <label class="form-check-label small" for="taxSgst">SGST @9% + CGST @9%</label>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="igstRow">
                                <span>IGST @18%:</span><span id="igstAmount">₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="sgstRow" style="display:none">
                                <span>SGST @9%:</span><span id="sgstAmount">₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="cgstRow" style="display:none">
                                <span>CGST @9%:</span><span id="cgstAmount">₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="h5 fw-bold">Grand Total:</span>
                                <span class="h4 text-success" id="grandTotal">₹0.00</span>
                            </div>
                            <div class="mt-2 border-top pt-2">
                                <small class="text-muted d-block">Amount (in words)</small>
                                <small class="fw-semibold" id="amountInWords">Zero Rupees Only</small>
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

            // ---- Repeater: Add row ----
            document.getElementById('addProduct').addEventListener('click', function() {
                productIndex++;
                const container = document.getElementById('productsContainer');
                const newRow = document.querySelector('.product-row').cloneNode(true);

                newRow.querySelectorAll('select, input, textarea, label').forEach(el => {
                    if (el.name) el.name = el.name.replace(/\[\d+\]/g, `[${productIndex}]`);
                    if (el.id && el.id.includes('pay_')) el.id = el.id.replace(/pay_\d+_/, `pay_${productIndex}_`);
                });
                newRow.querySelectorAll('label[for]').forEach(el => {
                    el.htmlFor = el.htmlFor.replace(/pay_\d+_/, `pay_${productIndex}_`);
                });

                // Reset values for the new row
                newRow.querySelector('.product-select').value = '';
                newRow.querySelector('.qty-input').value = '1';
                newRow.querySelector('.unit-price-input').value = '';
                newRow.querySelector('.total-price-input').value = '';
                newRow.querySelector('.subtotal').textContent = '₹0.00';
                newRow.querySelector('.subtotal').dataset.value = '0.00';
                newRow.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
                newRow.querySelectorAll('input[type=text]:not(.qty-input):not(.unit-price-input):not(.total-price-input), textarea').forEach(el => el.value = '');

                bindRow(newRow);
                container.appendChild(newRow);
            });

            // ---- Numeric input restrictions ----
            function numericOnly(el, decimals) {
                el.addEventListener('input', function() {
                    if (decimals) {
                        let v = this.value.replace(/[^0-9.]/g, '');
                        const parts = v.split('.');
                        v = parts.length > 1 ? parts[0] + '.' + parts.slice(1).join('').slice(0, 2) : v;
                        this.value = v;
                    } else {
                        this.value = this.value.replace(/[^0-9]/g, '');
                    }
                    const row = this.closest('.product-row');
                    recalcRow(row);
                });
            }

            function recalcRow(row) {
                const qty = parseInt(row.querySelector('.qty-input').value) || 0;
                const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
                const total = qty * unitPrice;
                row.querySelector('.total-price-input').value = total ? total.toFixed(2) : '';
                const subtotalEl = row.querySelector('.subtotal');
                subtotalEl.textContent = '₹' + total.toFixed(2);
                subtotalEl.dataset.value = total.toFixed(2);
                updateTotals();
            }

            function round2(v) { return Math.round(v * 100) / 100; }

            function inWords(num) {
                num = round2(num);
                const rupees = Math.floor(num);
                const paise = Math.round((num - rupees) * 100);
                const ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
                const tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
                const two = n => n < 20 ? ones[n] : (tens[Math.floor(n / 10)] + (n % 10 ? ' ' + ones[n % 10] : ''));
                const conv = n => {
                    if (n === 0) return 'Zero';
                    const parts = [];
                    const cr = Math.floor(n / 10000000); n %= 10000000;
                    const lk = Math.floor(n / 100000); n %= 100000;
                    const th = Math.floor(n / 1000); n %= 1000;
                    const hu = Math.floor(n / 100); n %= 100;
                    if (cr) parts.push(conv(cr) + ' Crore');
                    if (lk) parts.push(two(lk) + ' Lakh');
                    if (th) parts.push(two(th) + ' Thousand');
                    if (hu) parts.push(ones[hu] + ' Hundred');
                    if (n) parts.push(two(n));
                    return parts.join(' ');
                };
                let w = conv(rupees) + ' Rupees';
                if (paise > 0) w += ' And ' + two(paise) + ' Paise';
                return w + ' Only';
            }

            function applyTaxes(totalAmount) {
                const taxType = document.querySelector('input[name="tax_type"]:checked')?.value || 'igst';
                const igst = taxType === 'igst' ? round2(totalAmount * 0.18) : 0;
                const sgst = taxType === 'sgst_cgst' ? round2(totalAmount * 0.09) : 0;
                const cgst = taxType === 'sgst_cgst' ? round2(totalAmount * 0.09) : 0;
                const taxAmount = round2(igst + sgst + cgst);
                return { taxType, igst, sgst, cgst, taxAmount, grandTotal: round2(totalAmount + taxAmount) };
            }

            function updateTotals() {
                let totalItems = 0;
                let totalAmount = 0;
                document.querySelectorAll('.product-row').forEach(row => {
                    totalItems += parseInt(row.querySelector('.qty-input').value) || 0;
                    totalAmount += parseFloat(row.querySelector('.subtotal').dataset.value || 0);
                });
                const t = applyTaxes(totalAmount);
                document.getElementById('totalItems').textContent = totalItems;
                document.getElementById('totalAmount').textContent = '₹' + totalAmount.toFixed(2);
                document.getElementById('igstRow').style.display = t.taxType === 'igst' ? '' : 'none';
                document.getElementById('sgstRow').style.display = t.taxType === 'sgst_cgst' ? '' : 'none';
                document.getElementById('cgstRow').style.display = t.taxType === 'sgst_cgst' ? '' : 'none';
                document.getElementById('igstAmount').textContent = '₹' + t.igst.toFixed(2);
                document.getElementById('sgstAmount').textContent = '₹' + t.sgst.toFixed(2);
                document.getElementById('cgstAmount').textContent = '₹' + t.cgst.toFixed(2);
                document.getElementById('grandTotal').textContent = '₹' + t.grandTotal.toFixed(2);
                document.getElementById('amountInWords').textContent = inWords(t.grandTotal);
            }

            document.querySelectorAll('.tax-type').forEach(r => r.addEventListener('change', updateTotals));

            function bindRow(row) {
                numericOnly(row.querySelector('.qty-input'), false);
                numericOnly(row.querySelector('.unit-price-input'), true);
                numericOnly(row.querySelector('.total-price-input'), true);

                row.querySelector('.product-select').addEventListener('change', function() {
                    const price = this.selectedOptions[0]?.dataset.price || 0;
                    row.querySelector('.unit-price-input').value = price ? parseFloat(price).toFixed(2) : '';
                    recalcRow(row);
                });

                row.querySelector('.remove-row').onclick = function() {
                    if (document.querySelectorAll('.product-row').length > 1) {
                        this.closest('.product-row').remove();
                        updateTotals();
                    } else {
                        alert('At least one product row is required.');
                    }
                };
            }

            document.querySelectorAll('.product-row').forEach(bindRow);
            recalcRow(document.querySelector('.product-row'));

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
