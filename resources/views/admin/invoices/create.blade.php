@extends('layouts.app')

@section('title', 'Create Sales Invoice')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-file-invoice me-2 text-primary"></i>Create Sales Invoice
            </h4>
            <p class="text-muted mb-0">Generate Tax, Proforma, or Commercial Invoice from Sales Order or standalone</p>
        </div>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Invoices
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
        @csrf

        <!-- Source / Document Type Selection -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-link me-2 text-primary"></i>Source Document & Type
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Invoice Type <span class="text-danger">*</span></label>
                        <select name="type" id="invoice_type" class="form-select" required>
                            <option value="tax" {{ old('type') == 'tax' ? 'selected' : '' }}>Tax Invoice</option>
                            <option value="proforma" {{ old('type') == 'proforma' ? 'selected' : '' }}>Proforma Invoice</option>
                            <option value="commercial" {{ old('type') == 'commercial' ? 'selected' : '' }}>Commercial Invoice</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Generate From Sales Order</label>
                        <select name="sales_order_id" id="sales_order_id" class="form-select select2">
                            <option value="">-- Standalone (No Sales Order) --</option>
                            @foreach ($salesOrders as $so)
                                <option value="{{ $so->id }}" {{ old('sales_order_id', request('sales_order_id')) == $so->id ? 'selected' : '' }}>
                                    {{ $so->order_number }} &bull; {{ $so->customer?->name }} (₹{{ number_format($so->total_amount, 2) }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small text-muted">Selecting an order auto-populates customer and ordered items.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" name="invoice_number" id="invoice_number" class="form-control font-monospace fw-bold"
                            value="{{ old('invoice_number', $invoiceNumber) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="invoice_date" class="form-control"
                            value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header / Customer Info -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-user-tie me-2 text-primary"></i>Customer & Shipping Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customer_id" class="form-select select2" required>
                            <option value="">-- Select Customer --</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}"
                                    data-gst="{{ $c->gstin ?? $c->gst_no }}"
                                    data-address="{{ $c->address }}"
                                    data-state="{{ $c->state }}"
                                    {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Customer GSTIN</label>
                        <input type="text" name="customer_gst" id="customer_gst" class="form-control text-uppercase"
                            value="{{ old('customer_gst') }}" placeholder="27XXXXXXXXXXXXX">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Customer PO Number</label>
                        <input type="text" name="po_number" id="po_number" class="form-control"
                            value="{{ old('po_number') }}" placeholder="e.g. PO/2026/901">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Customer PO Date</label>
                        <input type="date" name="po_date" id="po_date" class="form-control"
                            value="{{ old('po_date') }}">
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Billing Address</label>
                        <textarea name="billing_address" id="billing_address" rows="2" class="form-control"
                            placeholder="Complete billing address">{{ old('billing_address') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Shipping Address</label>
                        <textarea name="shipping_address" id="shipping_address" rows="2" class="form-control"
                            placeholder="Delivery / site shipping address">{{ old('shipping_address') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Details -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-boxes me-2 text-primary"></i>Invoice Line Items
                </h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                    <i class="fas fa-plus me-1"></i>Add Item Row
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 align-middle" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">Item Name <span class="text-danger">*</span></th>
                                <th style="width: 15%;">HSN/SAC</th>
                                <th style="width: 10%;">Qty <span class="text-danger">*</span></th>
                                <th style="width: 10%;">Unit</th>
                                <th style="width: 12%;">Rate (₹) <span class="text-danger">*</span></th>
                                <th style="width: 10%;">Disc %</th>
                                <th style="width: 10%;">GST %</th>
                                <th style="width: 12%;" class="text-end">Amount (₹)</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Populated dynamically via JS or prefill -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light">
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="fw-semibold">Sub Total:</td>
                                <td class="text-end fw-bold" id="lblSubTotal">₹0.00</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Total Discount:</td>
                                <td class="text-end text-danger" id="lblDiscount">-₹0.00</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Taxable Amount:</td>
                                <td class="text-end fw-bold" id="lblTaxable">₹0.00</td>
                            </tr>
                            <tr>
                                <td>CGST:</td>
                                <td class="text-end" id="lblCgst">₹0.00</td>
                            </tr>
                            <tr>
                                <td>SGST:</td>
                                <td class="text-end" id="lblSgst">₹0.00</td>
                            </tr>
                            <tr>
                                <td>IGST:</td>
                                <td class="text-end" id="lblIgst">₹0.00</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">CESS (₹)</span>
                                        <input type="number" step="0.01" min="0" name="cess" id="inputCess"
                                            class="form-control" value="{{ old('cess', 0) }}">
                                    </div>
                                </td>
                                <td class="text-end" id="lblCess">₹0.00</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">TCS (₹)</span>
                                        <input type="number" step="0.01" min="0" name="tcs" id="inputTcs"
                                            class="form-control" value="{{ old('tcs', 0) }}">
                                    </div>
                                </td>
                                <td class="text-end" id="lblTcs">₹0.00</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Shipping (₹)</span>
                                        <input type="number" step="0.01" min="0" name="shipping_charge" id="inputShipping"
                                            class="form-control" value="{{ old('shipping_charge', 0) }}">
                                    </div>
                                </td>
                                <td class="text-end" id="lblShipping">₹0.00</td>
                            </tr>
                            <tr class="border-top border-dark">
                                <th class="fs-5">Grand Total:</th>
                                <th class="text-end fs-5 text-primary" id="lblGrandTotal">₹0.00</th>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terms & Notes -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Terms & Conditions</label>
                        <textarea name="terms" rows="3" class="form-control"
                            placeholder="Payment terms, delivery terms, warranty etc.">{{ old('terms', "1. Payment within 30 days.\n2. Goods once sold will not be taken back.") }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Notes / Remarks</label>
                        <textarea name="notes" rows="3" class="form-control"
                            placeholder="Internal or customer notes...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary btn-lg px-4">
                <i class="fas fa-save me-2"></i>Save Invoice (Draft)
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let rowIndex = 0;

function addRow(data = {}) {
    const i = rowIndex++;
    const tr = document.createElement('tr');
    tr.id = `row_${i}`;
    tr.innerHTML = `
        <td>
            <input type="text" name="items[${i}][item_name]" class="form-control form-control-sm mb-1 fw-semibold item-name"
                value="${data.item_name || ''}" placeholder="Product/Service Name" required>
            <input type="text" name="items[${i}][description]" class="form-control form-control-sm small"
                value="${data.description || ''}" placeholder="Description (optional)">
            <input type="hidden" name="items[${i}][product_id]" value="${data.product_id || ''}">
            <input type="hidden" name="items[${i}][sales_order_item_id]" value="${data.sales_order_item_id || ''}">
        </td>
        <td>
            <input type="text" name="items[${i}][hsn_code]" class="form-control form-control-sm text-uppercase"
                value="${data.hsn_code || ''}" placeholder="HSN/SAC">
        </td>
        <td>
            <input type="number" step="0.001" min="0.001" name="items[${i}][quantity]"
                class="form-control form-control-sm text-end item-qty" value="${data.quantity || 1}" required>
        </td>
        <td>
            <input type="text" name="items[${i}][unit]" class="form-control form-control-sm"
                value="${data.unit || 'Nos'}">
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="items[${i}][rate]"
                class="form-control form-control-sm text-end item-rate" value="${data.rate || 0}" required>
        </td>
        <td>
            <input type="number" step="0.01" min="0" max="100" name="items[${i}][discount_percent]"
                class="form-control form-control-sm text-end item-disc" value="${data.discount_percent || 0}">
        </td>
        <td>
            <select name="items[${i}][tax_percent]" class="form-select form-select-sm item-tax">
                <option value="0" ${data.tax_percent == 0 ? 'selected' : ''}>0%</option>
                <option value="5" ${data.tax_percent == 5 ? 'selected' : ''}>5%</option>
                <option value="12" ${data.tax_percent == 12 ? 'selected' : ''}>12%</option>
                <option value="18" ${data.tax_percent == 18 || !data.tax_percent ? 'selected' : ''}>18%</option>
                <option value="28" ${data.tax_percent == 28 ? 'selected' : ''}>28%</option>
            </select>
        </td>
        <td class="text-end fw-bold item-total">₹0.00</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(${i})">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    document.getElementById('itemsBody').appendChild(tr);

    tr.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', recalcTotals);
        el.addEventListener('change', recalcTotals);
    });

    recalcTotals();
}

function removeRow(id) {
    const el = document.getElementById(`row_${id}`);
    if (el) el.remove();
    recalcTotals();
}

function recalcTotals() {
    let subTotal = 0;
    let totalDiscount = 0;
    let totalTax = 0;

    document.querySelectorAll('#itemsBody tr').forEach(tr => {
        const qty = parseFloat(tr.querySelector('.item-qty')?.value || 0);
        const rate = parseFloat(tr.querySelector('.item-rate')?.value || 0);
        const discPct = parseFloat(tr.querySelector('.item-disc')?.value || 0);
        const taxPct = parseFloat(tr.querySelector('.item-tax')?.value || 0);

        const gross = qty * rate;
        const discAmt = gross * (discPct / 100);
        const taxable = Math.max(0, gross - discAmt);
        const taxAmt = taxable * (taxPct / 100);
        const lineTotal = taxable + taxAmt;

        subTotal += gross;
        totalDiscount += discAmt;
        totalTax += taxAmt;

        const totalCell = tr.querySelector('.item-total');
        if (totalCell) totalCell.innerText = '₹' + lineTotal.toFixed(2);
    });

    const taxableTotal = Math.max(0, subTotal - totalDiscount);
    const cess = parseFloat(document.getElementById('inputCess')?.value || 0);
    const tcs = parseFloat(document.getElementById('inputTcs')?.value || 0);
    const shipping = parseFloat(document.getElementById('inputShipping')?.value || 0);

    // Assume 50/50 split CGST/SGST for intra-state
    const cgst = totalTax / 2;
    const sgst = totalTax / 2;
    const igst = 0;

    const grandTotal = Math.round(taxableTotal + totalTax + cess + tcs + shipping);

    document.getElementById('lblSubTotal').innerText = '₹' + subTotal.toFixed(2);
    document.getElementById('lblDiscount').innerText = '-₹' + totalDiscount.toFixed(2);
    document.getElementById('lblTaxable').innerText = '₹' + taxableTotal.toFixed(2);
    document.getElementById('lblCgst').innerText = '₹' + cgst.toFixed(2);
    document.getElementById('lblSgst').innerText = '₹' + sgst.toFixed(2);
    document.getElementById('lblIgst').innerText = '₹' + igst.toFixed(2);
    document.getElementById('lblCess').innerText = '₹' + cess.toFixed(2);
    document.getElementById('lblTcs').innerText = '₹' + tcs.toFixed(2);
    document.getElementById('lblShipping').innerText = '₹' + shipping.toFixed(2);
    document.getElementById('lblGrandTotal').innerText = '₹' + grandTotal.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('addItemBtn').addEventListener('click', () => addRow());
    document.getElementById('inputCess').addEventListener('input', recalcTotals);
    document.getElementById('inputTcs').addEventListener('input', recalcTotals);
    document.getElementById('inputShipping').addEventListener('input', recalcTotals);

    // On customer change, fill GST and address
    document.getElementById('customer_id').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('customer_gst').value = opt.getAttribute('data-gst') || '';
            const addr = opt.getAttribute('data-address') || '';
            if (!document.getElementById('billing_address').value) document.getElementById('billing_address').value = addr;
            if (!document.getElementById('shipping_address').value) document.getElementById('shipping_address').value = addr;
        }
    });

    // On sales order change, fetch items via AJAX
    document.getElementById('sales_order_id').addEventListener('change', function() {
        const soId = this.value;
        if (!soId) return;

        fetch(`{{ url('invoices/fetch-sales-order') }}/${soId}`)
            .then(r => r.json())
            .then(data => {
                if (data.customer_id) {
                    $('#customer_id').val(data.customer_id).trigger('change');
                }
                if (data.customer_gst) document.getElementById('customer_gst').value = data.customer_gst;
                if (data.billing_address) document.getElementById('billing_address').value = data.billing_address;
                if (data.shipping_address) document.getElementById('shipping_address').value = data.shipping_address;
                if (data.po_number) document.getElementById('po_number').value = data.po_number;
                if (data.po_date) document.getElementById('po_date').value = data.po_date;

                // Clear current items and populate from SO
                document.getElementById('itemsBody').innerHTML = '';
                if (data.items && data.items.length) {
                    data.items.forEach(item => addRow(item));
                } else {
                    addRow();
                }
            })
            .catch(err => console.error('Error fetching SO:', err));
    });

    // Check if SO already selected on load
    const initialSo = document.getElementById('sales_order_id').value;
    if (initialSo) {
        document.getElementById('sales_order_id').dispatchEvent(new Event('change'));
    } else {
        addRow();
    }
});
</script>
@endpush
@endsection
