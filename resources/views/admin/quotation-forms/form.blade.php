@extends('layouts.app')

@section('title', ($mode === 'edit' ? 'Edit' : 'New') . ' Quotation Form')

@section('content')
    <div class="container-fluid py-3">
        <form id="quotationForm"
            action="{{ $mode === 'edit' ? route('quotation-forms.update', $form) : route('quotation-forms.store') }}"
            method="POST">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <!-- ============ HEADER SECTION ============ -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4 border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-file-signature me-2"></i>Quotation Form</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" required
                                value="{{ old('company_name', $form->company_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Certification / ISO Details</label>
                            <input type="text" name="certification" class="form-control"
                                value="{{ old('certification', $form->certification) }}" placeholder="An ISO 9001:2015 Certified Company">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Reference No. <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="ref_no" id="refNo" class="form-control" required
                                    value="{{ old('ref_no', $form->ref_no) }}">
                                <button type="button" class="btn btn-outline-secondary" id="regenRefNo" title="Regenerate">
                                    <i class="fas fa-rotate"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="form_date" id="formDate" class="form-control" required
                                value="{{ old('form_date', $form->form_date?->toDateString() ?? now()->toDateString()) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Document Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required
                                value="{{ old('title', $form->title) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ RECIPIENT SECTION ============ -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-building me-2 text-info"></i>Recipient Details <span class="text-danger">*</span></h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">To (Company Name) <span class="text-danger">*</span></label>
                            <input type="text" name="to_company" class="form-control" required
                                value="{{ old('to_company', $form->to_company) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="to_address" class="form-control" rows="2"
                                placeholder="Company address">{{ old('to_address', $form->to_address) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Project Details</label>
                            <textarea name="project_details" class="form-control" rows="2"
                                placeholder="Project name / site details">{{ old('project_details', $form->project_details) }}</textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Contact Person <span class="text-danger">*</span></label>
                            <input type="text" name="contact_person" class="form-control" required
                                value="{{ old('contact_person', $form->contact_person) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Designation</label>
                            <input type="text" name="contact_designation" class="form-control"
                                value="{{ old('contact_designation', $form->contact_designation) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ BODY SECTION ============ -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-align-left me-2 text-success"></i>Acknowledgement &amp; Product Specification</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Acknowledgement Text</label>
                            <textarea name="acknowledgement" class="form-control" rows="2"
                                placeholder="With reference to your enquiry...">{{ old('acknowledgement', $form->acknowledgement) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Product Specification / Type <span class="text-danger">*</span></label>
                            <input type="text" name="product_spec" class="form-control" required
                                placeholder="e.g. SLUICE GATE / OPEN CHANNEL GATE"
                                value="{{ old('product_spec', $form->product_spec) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ DESCRIPTION ENTRIES (MOC, MFG SPEC, TRIM...) ============ -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-list-ul me-2 text-warning"></i>Description <span class="text-danger">*</span>
                        <small class="text-muted fw-normal ms-2">(* marked fields are mandatory)</small>
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-success" id="addDescription">
                        <i class="fas fa-plus me-1"></i>Add Description
                    </button>
                </div>
                <div class="card-body p-4" id="descriptionContainer">
                    @php
                        $descDefaults = [
                            ['moc' => '', 'mfg_spec' => '', 'trim' => '', 'operation' => '', 'end_connection' => '', 'rating' => '', 'media' => ''],
                        ];
                        $descEntries = old('description_entries', $form->description_entries ?? $descDefaults);
                    @endphp
                    @foreach ($descEntries as $i => $entry)
                        <div class="description-entry border rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Description {{ $i + 1 }}</strong>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger remove-entry" title="Remove">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="row g-2">
                                @foreach ([
                                    'moc' => '* MOC (Material of Construction)',
                                    'mfg_spec' => '* MFG Spec (Manufacturing Specification)',
                                    'trim' => '* Trim',
                                    'operation' => '* Operation',
                                    'end_connection' => '* End Connection',
                                    'rating' => '* Rating',
                                    'media' => '* Media',
                                ] as $field => $label)
                                    <div class="col-md-6 col-lg-4">
                                        <label class="form-label small fw-semibold mb-1">{{ $label }}</label>
                                        <input type="text"
                                            name="description_entries[{{ $i }}][{{ $field }}]"
                                            class="form-control form-control-sm" required
                                            value="{{ $entry[$field] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- ============ ITEM TABLE ============ -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-table me-2 text-primary"></i>Item Table <span class="text-danger">*</span></h6>
                    <button type="button" class="btn btn-sm btn-outline-success" id="addItem">
                        <i class="fas fa-plus me-1"></i>Add Item Row
                    </button>
                </div>
                <div class="card-body p-4">
                    <table class="table table-bordered align-middle" id="itemTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:16%">Product Name <span class="text-danger">*</span></th>
                                <th style="width:12%">Size (MM)</th>
                                <th style="width:9%">Quantity (Nos)</th>
                                <th style="width:11%">Unit Price (Each No.)</th>
                                <th style="width:11%">Total Price</th>
                                <th style="width:33%">Payment Mode</th>
                                <th style="width:8%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemContainer">
                            @php
                                $itemDefaults = [['size_mm' => '', 'quantity' => 1, 'unit_price' => 0, 'total_price' => 0]];
                                $items = old('items', $form->items ?? $itemDefaults);
                            @endphp
                            @php
                                $paymentModes = ['LC', 'Credit', 'Advance', 'PIC', 'PDC', 'Proforma Invoice'];
                            @endphp
                            @foreach ($items as $i => $item)
                                <tr class="item-row">
                                    <td>
                                        <select name="items[{{ $i }}][product_name]" class="form-select form-select-sm product-name" required>
                                            <option value="" disabled {{ empty($item['product_name']) ? 'selected' : '' }}>-- Select Product --</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->name }}" {{ ($item['product_name'] ?? '') === $product->name ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="items[{{ $i }}][size_mm]" class="form-control form-control-sm" required
                                            value="{{ $item['size_mm'] ?? '' }}"></td>
                                    <td><input type="text" inputmode="numeric" pattern="[0-9]+" title="Whole numbers only"
                                            name="items[{{ $i }}][quantity]" class="form-control form-control-sm qty numeric-int" required
                                            value="{{ $item['quantity'] ?? 1 }}"></td>
                                    <td><input type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" title="Numbers with up to 2 decimal places"
                                            name="items[{{ $i }}][unit_price]" class="form-control form-control-sm price numeric-dec" required
                                            value="{{ number_format($item['unit_price'] ?? 0, 2, '.', '') }}"></td>
                                    <td><input type="text" name="items[{{ $i }}][total_price]"
                                            class="form-control form-control-sm row-total bg-body-tertiary" readonly
                                            value="{{ number_format($item['total_price'] ?? 0, 2, '.', '') }}"></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2 small">
                                            @foreach ($paymentModes as $mode)
                                                <label class="form-check mb-0" style="width: 48%">
                                                    <input type="checkbox" class="form-check-input"
                                                        name="items[{{ $i }}][payment_modes][]" value="{{ $mode }}"
                                                        {{ in_array($mode, $item['payment_modes'] ?? []) ? 'checked' : '' }}>
                                                    <span class="form-check-label">{{ $mode }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="4" class="text-end">Grand Total</td>
                                <td><input type="text" id="grandTotal" class="form-control form-control-sm bg-body-tertiary fw-bold" readonly
                                        value="{{ old('grand_total', $form->grand_total ?? 0) }}"></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- ============ TERMS & CONDITIONS ============ -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-file-contract me-2 text-danger"></i>Terms &amp; Conditions <span class="text-danger">*</span></h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Delivery Terms <span class="text-danger">*</span></label>
                            <input type="text" name="delivery_terms" class="form-control" required
                                placeholder="e.g. F.O.R. site / Ex Works"
                                value="{{ old('delivery_terms', $form->delivery_terms) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Warranty Terms <span class="text-danger">*</span></label>
                            <input type="text" name="warranty_terms" class="form-control" required
                                placeholder="e.g. 18 months from supply or 12 months from commissioning"
                                value="{{ old('warranty_terms', $form->warranty_terms) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">IGST %</label>
                            <input type="number" step="0.01" min="0" max="100" name="igst_percent" class="form-control"
                                value="{{ old('igst_percent', $form->igst_percent ?? 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">SGST %</label>
                            <input type="number" step="0.01" min="0" max="100" name="sgst_percent" class="form-control"
                                value="{{ old('sgst_percent', $form->sgst_percent ?? 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">CGST %</label>
                            <input type="number" step="0.01" min="0" max="100" name="cgst_percent" class="form-control"
                                value="{{ old('cgst_percent', $form->cgst_percent ?? 0) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Payment Terms <span class="text-danger">*</span></label>
                            <textarea name="payment_terms" class="form-control" rows="3" required
                                placeholder="e.g. 30% advance with PO, 60% before dispatch, 10% after commissioning">{{ old('payment_terms', $form->payment_terms) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Inspection — Vendor Scope</label>
                            <textarea name="inspection_vendor_scope" class="form-control" rows="2">{{ old('inspection_vendor_scope', $form->inspection_vendor_scope) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Inspection — Third Party Scope</label>
                            <textarea name="inspection_third_party_scope" class="form-control" rows="2">{{ old('inspection_third_party_scope', $form->inspection_third_party_scope) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ FOOTER SECTION ============ -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-pen-nib me-2 text-secondary"></i>Notes &amp; Signatory</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes (multi-line)</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="Any additional notes...">{{ old('notes', $form->notes) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Closing Statement</label>
                            <input type="text" name="closing_statement" class="form-control"
                                value="{{ old('closing_statement', $form->closing_statement ?? 'Thanking You') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Authorized Signatory (Company) <span class="text-danger">*</span></label>
                            <input type="text" name="signatory_company" class="form-control" required
                                value="{{ old('signatory_company', $form->signatory_company ?? $form->company_name) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Designation <span class="text-danger">*</span></label>
                            <input type="text" name="signatory_designation" class="form-control" required
                                placeholder="e.g. Managing Director"
                                value="{{ old('signatory_designation', $form->signatory_designation) }}">
                        </div>
                        <div class="col-12">
                            <input type="hidden" name="status" value="{{ old('status', $form->status ?? 'draft') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ ACTIONS ============ -->
            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="{{ $mode === 'edit' ? route('quotation-forms.show', $form) : route('quotation-forms.index') }}"
                    class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>{{ $mode === 'edit' ? 'Update' : 'Save' }} Quotation Form
                </button>
            </div>
        </form>
    </div>

    <template id="descriptionTemplate">
        <div class="description-entry border rounded-3 p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong class="desc-index">Description</strong>
                <button type="button" class="btn btn-sm btn-outline-danger remove-entry"><i class="fas fa-trash"></i></button>
            </div>
            <div class="row g-2">
                @foreach (['moc' => '* MOC (Material of Construction)', 'mfg_spec' => '* MFG Spec (Manufacturing Specification)', 'trim' => '* Trim', 'operation' => '* Operation', 'end_connection' => '* End Connection', 'rating' => '* Rating', 'media' => '* Media'] as $f => $l)
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label small fw-semibold mb-1">{{ $l }}</label>
                        <input type="text" class="form-control form-control-sm" data-field="{{ $f }}" required>
                    </div>
                @endforeach
            </div>
        </div>
    </template>

    <template id="itemTemplate">
        <tr class="item-row">
            <td>
                <select class="form-select form-select-sm product-name" required>
                    <option value="" disabled selected>-- Select Product --</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->name }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm" data-field="size_mm" required></td>
            <td><input type="text" inputmode="numeric" pattern="[0-9]+" title="Whole numbers only" class="form-control form-control-sm qty numeric-int" data-field="quantity" value="1" required></td>
            <td><input type="text" inputmode="decimal" pattern="\d+(\.\d{1,2})?" title="Numbers with up to 2 decimal places" class="form-control form-control-sm price numeric-dec" data-field="unit_price" value="0.00" required></td>
            <td><input type="text" class="form-control form-control-sm row-total bg-body-tertiary" readonly value="0.00"></td>
            <td>
                <div class="d-flex flex-wrap gap-2 small">
                    @foreach (['LC', 'Credit', 'Advance', 'PIC', 'PDC', 'Proforma Invoice'] as $mode)
                        <label class="form-check mb-0" style="width: 48%">
                            <input type="checkbox" class="form-check-input" name="items[0][payment_modes][]" value="{{ $mode }}">
                            <span class="form-check-label">{{ $mode }}</span>
                        </label>
                    @endforeach
                </div>
            </td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-trash"></i></button></td>
        </tr>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const descContainer = document.getElementById('descriptionContainer');
            const itemContainer = document.getElementById('itemContainer');

            // ---------- Index renaming ----------
            function reindex(container, rowSelector, namePrefix) {
                container.querySelectorAll(rowSelector).forEach((row, i) => {
                    row.querySelectorAll('[name]').forEach(el => {
                        el.name = el.name.replace(/\[\d+\]/, '[' + i + ']');
                    });
                    if (namePrefix === 'description_entries') {
                        const idx = row.querySelector('.desc-index');
                        if (idx) idx.textContent = 'Description ' + (i + 1);
                    }
                });
            }

            // ---------- Add / remove description entries ----------
            document.getElementById('addDescription').addEventListener('click', function () {
                const tpl = document.getElementById('descriptionTemplate').content.cloneNode(true);
                descContainer.appendChild(tpl);
                reindex(descContainer, '.description-entry', 'description_entries');
            });

            descContainer.addEventListener('click', function (e) {
                if (e.target.closest('.remove-entry')) {
                    const entries = descContainer.querySelectorAll('.description-entry');
                    if (entries.length <= 1) return; // keep at least one
                    e.target.closest('.description-entry').remove();
                    reindex(descContainer, '.description-entry', 'description_entries');
                }
            });

            // ---------- Add / remove item rows + auto-calc ----------
            document.getElementById('addItem').addEventListener('click', function () {
                const tpl = document.getElementById('itemTemplate').content.cloneNode(true);
                itemContainer.appendChild(tpl);
                reindex(itemContainer, '.item-row', 'items');
            });

            // Numeric-only enforcement: integers for qty, up to 2 decimals for prices
            function sanitizeNumericInputs(scope) {
                scope.querySelectorAll('.numeric-int').forEach(el => {
                    el.addEventListener('input', function () {
                        this.value = this.value.replace(/[^0-9]/g, '');
                        recalcAll();
                    });
                });
                scope.querySelectorAll('.numeric-dec').forEach(el => {
                    el.addEventListener('input', function () {
                        let v = this.value.replace(/[^0-9.]/g, '');
                        const parts = v.split('.');
                        if (parts.length > 2) v = parts[0] + '.' + parts.slice(1).join('');
                        const dec = v.split('.');
                        if (dec[1] && dec[1].length > 2) v = dec[0] + '.' + dec[1].slice(0, 2);
                        this.value = v;
                        recalcAll();
                    });
                });
            }
            sanitizeNumericInputs(itemContainer);
            document.getElementById('addItem').addEventListener('click', function () {
                sanitizeNumericInputs(itemContainer);
            });

            itemContainer.addEventListener('click', function (e) {
                if (e.target.closest('.remove-row')) {
                    const rows = itemContainer.querySelectorAll('.item-row');
                    if (rows.length <= 1) return; // keep at least one row
                    e.target.closest('.item-row').remove();
                    reindex(itemContainer, '.item-row', 'items');
                }
            });

            function recalcRow(row) {
                const qty = parseFloat(row.querySelector('.qty')?.value || 0);
                const price = parseFloat(row.querySelector('.price')?.value || 0);
                const total = (qty * price).toFixed(2);
                const totalEl = row.querySelector('.row-total');
                if (totalEl) totalEl.value = total;
            }

            function recalcAll() {
                let grand = 0;
                itemContainer.querySelectorAll('.item-row').forEach(row => {
                    recalcRow(row);
                    grand += parseFloat(row.querySelector('.row-total').value || 0);
                });
                document.getElementById('grandTotal').value = grand.toFixed(2);
            }

            itemContainer.addEventListener('input', recalcAll);
            recalcAll();

            // ---------- Auto-generate reference number NES/YY-YY/QTN-XXX ----------
            function fiscalYear(d) {
                let start = d.getMonth() >= 3 ? d.getFullYear() : d.getFullYear() - 1;
                return String(start % 100).padStart(2, '0') + '-' + String((start + 1) % 100).padStart(2, '0');
            }

            const refInput = document.getElementById('refNo');
            document.getElementById('formDate').addEventListener('change', function () {
                const fy = fiscalYear(new Date(this.value));
                if (/^NES\/[\d\-]{5}\/QTN-/.test(refInput.value)) {
                    refInput.value = refInput.value.replace(/^NES\/[\d\-]{5}\//, 'NES/' + fy + '/');
                }
            });

            document.getElementById('regenRefNo').addEventListener('click', function () {
                const fy = fiscalYear(new Date(document.getElementById('formDate').value || Date.now()));
                const seq = String(Math.floor(Math.random() * 900) + 100).padStart(3, '0');
                refInput.value = 'NES/' + fy + '/QTN-' + seq;
            });

            // ---------- Client-side validation feedback ----------
            document.getElementById('quotationForm').addEventListener('submit', function (e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                this.classList.add('was-validated');
            });
        });
    </script>
@endsection
