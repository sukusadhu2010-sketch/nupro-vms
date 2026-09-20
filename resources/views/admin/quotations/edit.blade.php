@extends('layouts.app')

@section('title', 'Edit Quotation')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('enquiries.index') }}">Enquiries</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('enquiries.show', $quotation->enquiry) }}">#{{ $quotation->enquiry->id }}</a>
                        </li>
                        <li class="breadcrumb-item active">Edit {{ $quotation->quote_number }}</li>
                    </ol>
                </nav>
                <h1 class="h2 mb-1">
                    <i class="fas fa-edit me-2 text-warning"></i>Edit {{ $quotation->quote_number }}
                </h1>
            </div>
        </div>

        <form method="POST" action="{{ route('quotations.update', $quotation) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-lg">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Quotation Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Quote Number</label>
                                    <input type="text" class="form-control" value="{{ $quotation->quote_number }}"
                                        disabled>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Version</label>
                                    <input type="text" class="form-control" value="V{{ $quotation->version }}" disabled>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Valid Until</label>
                                    <input type="date" name="valid_until" class="form-control"
                                        value="{{ $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($quotation->status) }}"
                                        disabled>
                                </div>
                            </div>

                            <!-- ============ ACKNOWLEDGEMENT & PRODUCT SPEC ============ -->
                            <div class="card shadow-sm border-0 rounded-4 mb-4 mt-4">
                                <div class="card-header bg-light rounded-top-4">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-align-left me-2 text-success"></i>Acknowledgement &amp; Product Specification</h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Acknowledgement Text</label>
                                            <textarea name="acknowledgement" class="form-control" rows="2"
                                                placeholder="With reference to your enquiry...">{{ old('acknowledgement', $quotation->acknowledgement) }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Customer Information</label>
                                            <textarea name="customer_information" class="form-control" rows="3"
                                                placeholder="Customer address, contact and other details...">{{ old('customer_information', $quotation->customer_information) }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Kind Atten.</label>
                                            <input type="text" name="kind_attention" class="form-control"
                                                placeholder="e.g. Mr. Sharma — Purchase Department"
                                                value="{{ old('kind_attention', $quotation->kind_attention) }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Product Specification / Type <span class="text-danger">*</span></label>
                                            <input type="text" name="product_spec" class="form-control" required
                                                placeholder="e.g. SLUICE GATE / OPEN CHANNEL GATE"
                                                value="{{ old('product_spec', $quotation->product_spec) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <label class="form-label fw-bold mb-3">Quote Items <span class="text-danger">*</span></label>
                            <div id="itemsContainer">
                                @foreach ($quotation->items as $index => $item)
                                    <div class="item-row row mb-3 border p-3 rounded bg-light">
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Product Name <span class="text-danger">*</span></label>
                                            <select class="form-select product-select select2"
                                                name="items[{{ $index }}][product_id]" required data-placeholder="Select Product">
                                                <option value=""></option>
                                                @foreach (App\Models\Product::with('productCategory')->where('status', 'active')->get() as $p)
                                                    <option value="{{ $p->id }}" data-price="{{ $p->price }}"
                                                        {{ $p->id == $item->product_id ? 'selected' : '' }}>
                                                        {{ $p->name }} - {{ $p->productCategory?->name ?? $p->category ?? 'General' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label fw-bold small">Quantity <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control qty-input" inputmode="numeric"
                                                name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Unit Price</label>
                                            <input type="text" class="form-control unit-price-input" inputmode="decimal"
                                                name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold small">Total Price</label>
                                            <input type="text" class="form-control total-price-input" inputmode="decimal"
                                                name="items[{{ $index }}][total_price]" value="{{ $item->total_price }}" readonly>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <div class="description-entry border rounded-3 p-3 bg-white">
                                                <div class="mb-2">
                                                    <strong class="small"><i class="fas fa-list-ul me-1 text-warning"></i>Additional Informations</strong>
                                                    <small class="text-muted ms-2">(* marked fields are mandatory)</small>
                                                </div>
                                                <div class="row g-2">
                                                    @foreach (['moc' => '* MOC (Material of Construction)', 'mfg_spec' => '* MFG Spec (Manufacturing Specification)', 'trim' => '* Trim', 'operation' => '* Operation', 'end_connection' => '* End Connection', 'rating' => '* Rating', 'media' => '* Media'] as $field => $descLabel)
                                                        <div class="col-md-6 col-lg-4">
                                                            <label class="form-label small fw-semibold mb-1">{{ $descLabel }}</label>
                                                            <input type="text" class="form-control form-control-sm" name="items[{{ $index }}][{{ $field }}]" value="{{ $item->{$field} ?? '' }}" required>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label class="form-label fw-bold small">Remarks</label>
                                            <textarea class="form-control" name="items[{{ $index }}][remarks]" rows="2" placeholder="Remarks">{{ $item->remarks ?? $item->notes }}</textarea>
                                        </div>
                                        <div class="col-12 mt-2 d-flex justify-content-end">
                                            <div class="subtotal me-3 fw-bold">₹0.00</div>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="addItem" class="btn btn-outline-success">
                                <i class="fas fa-plus"></i> Add Item
                            </button>



                            <!-- ============ TERMS & CONDITIONS ============ -->
                            <div class="card shadow-sm border-0 rounded-4 mb-4">
                                <div class="card-header bg-light rounded-top-4">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-file-contract me-2 text-danger"></i>Terms &amp; Conditions <span class="text-danger">*</span></h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Delivery Terms <span class="text-danger">*</span></label>
                                            <input type="text" name="delivery_terms" class="form-control" required
                                                placeholder="e.g. F.O.R. site / Ex Works"
                                                value="{{ old('delivery_terms', $quotation->delivery_terms) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Warranty Terms <span class="text-danger">*</span></label>
                                            <input type="text" name="warranty_terms" class="form-control" required
                                                placeholder="e.g. 18 months from supply or 12 months from commissioning"
                                                value="{{ old('warranty_terms', $quotation->warranty_terms) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">PRICES BASIS</label>
                                            <div class="d-flex gap-2">
                                                <input type="text" name="prices_basis" class="form-control"
                                                    placeholder="e.g. Prices are based on..." value="{{ old('prices_basis', $quotation->prices_basis) }}">
                                                <div class="d-flex flex-column justify-content-center gap-1">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="prices_basis_option" value="ex-works" id="pricesExWorksEdit"
                                                            {{ old('prices_basis_option', $quotation->prices_basis_option) === 'ex-works' ? 'checked' : '' }}>
                                                        <label class="form-check-label small" for="pricesExWorksEdit">Ex-Workers</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="prices_basis_option" value="transported-godown" id="pricesGodownEdit"
                                                            {{ old('prices_basis_option', $quotation->prices_basis_option) === 'transported-godown' ? 'checked' : '' }}>
                                                        <label class="form-check-label small" for="pricesGodownEdit">Transported Godown</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Payment Terms <span class="text-danger">*</span></label>
                                            @foreach (['LC / Credit' => 'lc_credit', 'Advance + PI' => 'advance_pi', 'PDC' => 'pdc', 'Proforma Invoice' => 'proforma_invoice'] as $label => $key)
                                                <div class="d-flex gap-2 align-items-center mb-2">
                                                    <div class="form-check" style="min-width: 160px;">
                                                        <input class="form-check-input payment-term-option" type="radio" name="payment_term_option" value="{{ $key }}"
                                                            id="payterm_edit_{{ $key }}" {{ old('payment_term_option', $quotation->payment_term_option) === $key ? 'checked' : '' }}>
                                                        <label class="form-check-label small" for="payterm_edit_{{ $key }}">{{ $label }}</label>
                                                    </div>
                                                    <input type="text" class="form-control form-control-sm payment-term-text"
                                                        name="payment_term_text[{{ $key }}]" placeholder="Details for {{ $label }}..."
                                                        value="{{ old('payment_term_text.' . $key, $quotation->payment_term_text[$key] ?? '') }}">
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Inspection — Vendor Scope</label>
                                            <textarea name="inspection_vendor_scope" class="form-control" rows="2">{{ old('inspection_vendor_scope', $quotation->inspection_vendor_scope) }}</textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Inspection — Third Party Scope</label>
                                            <textarea name="inspection_third_party_scope" class="form-control" rows="2">{{ old('inspection_third_party_scope', $quotation->inspection_third_party_scope) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ============ NOTES & SIGNATORY ============ -->
                            <div class="card shadow-sm border-0 rounded-4 mb-4">
                                <div class="card-header bg-light rounded-top-4">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-pen-nib me-2 text-secondary"></i>Notes &amp; Signatory</h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Notes</label>
                                            <div id="notes-editor" style="min-height: 140px; background: #fff;">{!! $quotation->notes !!}</div>
                                            <input type="hidden" name="notes" id="notes-input" value="">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Closing Statement</label>
                                            <input type="text" name="closing_statement" class="form-control"
                                                value="{{ old('closing_statement', $quotation->closing_statement ?? 'Thanking You') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Authorized Signatory (Company) <span class="text-danger">*</span></label>
                                            <input type="text" name="signatory_company" class="form-control" required
                                                value="{{ old('signatory_company', $quotation->signatory_company ?? \App\Models\OrganizationSetting::current()->name) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Designation <span class="text-danger">*</span></label>
                                            <input type="text" name="signatory_designation" class="form-control" required
                                                placeholder="e.g. Managing Director"
                                                value="{{ old('signatory_designation', $quotation->signatory_designation) }}">
                                        </div>
                                    </div>
                                </div>
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
                                <span id="totalItems">{{ $quotation->items->sum('quantity') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Amount (Qty):</span>
                                <span id="totalAmount">₹{{ number_format($quotation->total_amount, 2) }}</span>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold small mb-1">Tax Details</label>
                                <div class="form-check">
                                    <input class="form-check-input tax-type" type="radio" name="tax_type" value="igst" id="taxIgst" {{ ($quotation->tax_type ?? 'igst') === 'igst' ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="taxIgst">IGST @18%</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input tax-type" type="radio" name="tax_type" value="sgst_cgst" id="taxSgst" {{ ($quotation->tax_type ?? '') === 'sgst_cgst' ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="taxSgst">SGST @9% + CGST @9%</label>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="igstRow" style="display:{{ ($quotation->tax_type ?? 'igst') === 'igst' ? '' : 'none' }}">
                                <span>IGST @18%:</span><span id="igstAmount">₹{{ number_format($quotation->igst ?? 0, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="sgstRow" style="display:{{ ($quotation->tax_type ?? '') === 'sgst_cgst' ? '' : 'none' }}">
                                <span>SGST @9%:</span><span id="sgstAmount">₹{{ number_format($quotation->sgst ?? 0, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2" id="cgstRow" style="display:{{ ($quotation->tax_type ?? '') === 'sgst_cgst' ? '' : 'none' }}">
                                <span>CGST @9%:</span><span id="cgstAmount">₹{{ number_format($quotation->cgst ?? 0, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="h5 fw-bold">Grand Total:</span>
                                <span class="h4 text-success" id="grandTotal">₹{{ number_format($quotation->total_amount + ($quotation->tax_amount ?? 0), 2) }}</span>
                            </div>
                            <div class="mt-2 border-top pt-2">
                                <small class="text-muted d-block">Amount (in words)</small>
                                <small class="fw-semibold" id="amountInWords">{{ \App\Support\Amount::inWords($quotation->total_amount + ($quotation->tax_amount ?? 0)) }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-paperclip me-2"></i>Attachments</h6>
                        </div>
                        <div class="card-body">
                            @if ($quotation->attachments && count($quotation->attachments))
                                <div class="mb-3">
                                    <small class="text-muted fw-bold">Existing Files:</small>
                                    <div class="list-group list-group-flush mt-2">
                                        @foreach ($quotation->attachments as $attachment)
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

                    <div class="card shadow-sm mt-3">
                        <div class="card-body text-center py-4">
                            <button type="submit" class="btn btn-warning btn-lg w-100">
                                <i class="fas fa-save me-2"></i>Update Quotation
                            </button>
                            <a href="{{ route('quotations.show', $quotation) }}"
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
            let itemIndex = {{ count($quotation->items) }};

            document.getElementById('addItem').addEventListener('click', function() {
                itemIndex++;
                const container = document.getElementById('itemsContainer');
                const newRow = document.querySelector('.item-row').cloneNode(true);

                newRow.querySelectorAll('select, input, textarea, label').forEach(el => {
                    if (el.name) el.name = el.name.replace(/\[\d+\]/g, `[${itemIndex}]`);
                    if (el.id && el.id.includes('pay_')) el.id = el.id.replace(/pay_\d+_/, `pay_${itemIndex}_`);
                });
                newRow.querySelectorAll('label[for]').forEach(el => {
                    el.htmlFor = el.htmlFor.replace(/pay_\d+_/, `pay_${itemIndex}_`);
                });

                newRow.querySelector('.product-select').value = '';
                newRow.querySelector('.qty-input').value = '1';
                newRow.querySelector('.unit-price-input').value = '';
                newRow.querySelector('.total-price-input').value = '';
                newRow.querySelector('.subtotal').textContent = '₹0.00';
                newRow.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
                newRow.querySelectorAll('input[type=text]:not(.qty-input):not(.unit-price-input):not(.total-price-input), textarea').forEach(el => el.value = '');

                // Re-init Select2 on the cloned select
                const clonedSelect = newRow.querySelector('.product-select');
                if (window.jQuery && clonedSelect) {
                    jQuery(clonedSelect).select2({ width: '100%', placeholder: 'Select Product', allowClear: true });
                }

                bindRow(newRow);
                container.appendChild(newRow);
            });

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
                    recalcRow(this.closest('.item-row'));
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
                document.querySelectorAll('.item-row').forEach(row => {
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
                    if (document.querySelectorAll('.item-row').length > 1) {
                        this.closest('.item-row').remove();
                        updateTotals();
                    } else {
                        alert('At least one item row is required.');
                    }
                };
            }

            document.querySelectorAll('.item-row').forEach(row => {
                bindRow(row);
                recalcRow(row);
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

        <script>
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
        </script>

        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
        <script>
            const notesInput = document.getElementById('notes-input');
            const quill = new Quill('#notes-editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }, { 'font': [] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        [{ 'align': [] }],
                        ['link'],
                        ['clean']
                    ]
                }
            });
            quill.root.style.minHeight = '140px';
            quill.on('text-change', function () {
                notesInput.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
            });
            document.querySelector('form').addEventListener('submit', function () {
                notesInput.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
            });
        </script>
    @endpush
@endsection
