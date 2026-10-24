@extends('layouts.app')

@section('title', 'Edit Invoice - ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-edit me-2 text-primary"></i>Edit Invoice #{{ $invoice->invoice_number }}
            </h4>
            <p class="text-muted mb-0">Update invoice details, item lines, or tax adjustments</p>
        </div>
        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Invoice
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

    <form action="{{ route('invoices.update', $invoice) }}" method="POST" id="invoiceForm">
        @csrf
        @method('PUT')

        <!-- Header -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Invoice Header
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Invoice Number</label>
                        <input type="text" class="form-control font-monospace fw-bold bg-light"
                            value="{{ $invoice->invoice_number }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Invoice Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="tax" {{ old('type', $invoice->type) == 'tax' ? 'selected' : '' }}>Tax Invoice</option>
                            <option value="proforma" {{ old('type', $invoice->type) == 'proforma' ? 'selected' : '' }}>Proforma Invoice</option>
                            <option value="commercial" {{ old('type', $invoice->type) == 'commercial' ? 'selected' : '' }}>Commercial Invoice</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="invoice_date" class="form-control"
                            value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customer_id" class="form-select select2" required>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id', $invoice->customer_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Customer GSTIN</label>
                        <input type="text" name="customer_gst" class="form-control text-uppercase"
                            value="{{ old('customer_gst', $invoice->customer_gst) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Customer PO Number</label>
                        <input type="text" name="po_number" class="form-control"
                            value="{{ old('po_number', $invoice->po_number) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Customer PO Date</label>
                        <input type="date" name="po_date" class="form-control"
                            value="{{ old('po_date', $invoice->po_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Billing Address</label>
                        <textarea name="billing_address" rows="2" class="form-control">{{ old('billing_address', $invoice->billing_address) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Shipping Address</label>
                        <textarea name="shipping_address" rows="2" class="form-control">{{ old('shipping_address', $invoice->shipping_address) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items -->
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
                            <!-- Populated via JS -->
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
                                            class="form-control" value="{{ old('cess', $invoice->cess) }}">
                                    </div>
                                </td>
                                <td class="text-end" id="lblCess">₹0.00</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">TCS (₹)</span>
                                        <input type="number" step="0.01" min="0" name="tcs" id="inputTcs"
                                            class="form-control" value="{{ old('tcs', $invoice->tcs) }}">
                                    </div>
                                </td>
                                <td class="text-end" id="lblTcs">₹0.00</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Shipping (₹)</span>
                                        <input type="number" step="0.01" min="0" name="shipping_charge" id="inputShipping"
                                            class="form-control" value="{{ old('shipping_charge', $invoice->shipping_charge) }}">
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

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Terms & Conditions</label>
                        <textarea name="terms" rows="3" class="form-control">{{ old('terms', $invoice->terms) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Notes / Remarks</label>
                        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary btn-lg px-4">
                <i class="fas fa-save me-2"></i>Update Invoice
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let rowIndex = 0;
const initialItems = @json($invoice->items);

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

    if (initialItems && initialItems.length) {
        initialItems.forEach(item => addRow(item));
    } else {
        addRow();
    }
});
</script>
@endpush
@endsection

