@extends('layouts.app')

@section('title', 'Record Customer Receipt')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="fas fa-hand-holding-usd me-2 text-success"></i>Record Customer Receipt
            </h4>
            <p class="text-muted mb-0">Record collection from customer and allocate against outstanding invoices</p>
        </div>
        <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Receipts
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

    <form action="{{ route('receipts.store') }}" method="POST" id="receiptForm">
        @csrf

        <div class="row g-4 mb-4">
            <!-- Receipt Header -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-receipt me-2 text-success"></i>Receipt Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Receipt Number <span class="text-danger">*</span></label>
                                <input type="text" name="payment_number" class="form-control font-monospace fw-bold"
                                    value="{{ old('payment_number', $receiptNumber) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Receipt Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control"
                                    value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="form-select select2" required>
                                    <option value="">-- Choose Customer --</option>
                                    @foreach ($customers as $c)
                                        <option value="{{ $c->id }}" {{ old('customer_id', $selectedCustomerId) == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Received Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" min="0.01" name="amount" id="amount"
                                        class="form-control fw-bold fs-5 text-success" value="{{ old('amount') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                                <select name="payment_mode" id="payment_mode" class="form-select" required>
                                    <option value="bank_transfer" {{ old('payment_mode') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="neft" {{ old('payment_mode') == 'neft' ? 'selected' : '' }}>NEFT</option>
                                    <option value="rtgs" {{ old('payment_mode') == 'rtgs' ? 'selected' : '' }}>RTGS</option>
                                    <option value="upi" {{ old('payment_mode') == 'upi' ? 'selected' : '' }}>UPI</option>
                                    <option value="cheque" {{ old('payment_mode') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="cash" {{ old('payment_mode') == 'cash' ? 'selected' : '' }}>Cash</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction Reference Details -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-university me-2 text-primary"></i>Bank / Instrument Reference
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Reference / UTR / Cheque #</label>
                                <input type="text" name="reference_number" class="form-control"
                                    value="{{ old('reference_number') }}" placeholder="e.g. UTR12345678 or CHQ0012">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control"
                                    value="{{ old('bank_name') }}" placeholder="e.g. HDFC Bank">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cheque / Value Date</label>
                                <input type="date" name="cheque_date" class="form-control"
                                    value="{{ old('cheque_date') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Remarks / Notes</label>
                                <textarea name="remarks" rows="2" class="form-control"
                                    placeholder="Optional notes or receipt particulars...">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Allocation Section -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-tasks me-2 text-primary"></i>Invoice Allocation
                    </h5>
                    <small class="text-muted">Allocate payment against pending invoices (supports single, multiple, or partial payments)</small>
                </div>
                <div class="text-end">
                    <span class="small text-muted me-2">Total Customer Outstanding:</span>
                    <span class="fw-bold text-danger fs-6" id="lblCustOutstanding">₹0.00</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 align-middle" id="invoicesTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">Select</th>
                                <th>Invoice Number</th>
                                <th>Invoice Date</th>
                                <th class="text-end">Invoice Amount</th>
                                <th class="text-end">Paid Amount</th>
                                <th class="text-end">Outstanding Due</th>
                                <th style="width: 200px;" class="text-end">Allocate Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody id="invoicesBody">
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Please select a customer above to view unpaid invoices.
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="6" class="text-end">Total Allocated Amount:</th>
                                <th class="text-end fs-6 fw-bold text-success" id="lblTotalAllocated">₹0.00</th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-end">Unallocated Advance Balance:</th>
                                <th class="text-end fs-6 fw-bold text-info" id="lblUnallocated">₹0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-success btn-lg px-4" id="saveReceiptBtn">
                <i class="fas fa-check-circle me-2"></i>Save Customer Receipt
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
const preselectedInvoiceId = '{{ $selectedInvoiceId }}';

function loadCustomerInvoices(customerId) {
    if (!customerId) {
        document.getElementById('invoicesBody').innerHTML = `
            <tr><td colspan="7" class="text-center py-4 text-muted">Please select a customer above.</td></tr>
        `;
        document.getElementById('lblCustOutstanding').innerText = '₹0.00';
        recalcAllocation();
        return;
    }

    document.getElementById('invoicesBody').innerHTML = `
        <tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading customer invoices...</td></tr>
    `;

    fetch(`{{ url('receipts/fetch-customer-invoices') }}/${customerId}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('lblCustOutstanding').innerText = '₹' + data.total_outstanding.toFixed(2);
            const tbody = document.getElementById('invoicesBody');
            tbody.innerHTML = '';

            if (!data.invoices || data.invoices.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="7" class="text-center py-4 text-muted">No pending unpaid invoices for this customer. Any amount recorded will be treated as an unallocated advance.</td></tr>
                `;
                recalcAllocation();
                return;
            }

            data.invoices.forEach((inv, idx) => {
                const tr = document.createElement('tr');
                const isPreselected = preselectedInvoiceId && String(inv.id) === String(preselectedInvoiceId);
                const defaultAlloc = isPreselected ? inv.outstanding : '';

                tr.innerHTML = `
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input invoice-check" data-idx="${idx}" ${isPreselected ? 'checked' : ''}>
                    </td>
                    <td class="fw-semibold font-monospace">
                        ${inv.invoice_number}
                        <input type="hidden" name="allocations[${idx}][invoice_id]" value="${inv.id}" ${isPreselected ? '' : 'disabled'}>
                    </td>
                    <td class="small">${inv.invoice_date}</td>
                    <td class="text-end font-monospace">₹${inv.grand_total.toFixed(2)}</td>
                    <td class="text-end font-monospace text-success">₹${inv.paid_amount.toFixed(2)}</td>
                    <td class="text-end font-monospace fw-bold text-danger">₹${inv.outstanding.toFixed(2)}</td>
                    <td>
                        <input type="number" step="0.01" min="0.01" max="${inv.outstanding}"
                            name="allocations[${idx}][allocated_amount]"
                            class="form-control form-control-sm text-end alloc-input font-monospace"
                            value="${defaultAlloc}"
                            placeholder="0.00"
                            data-max="${inv.outstanding}"
                            ${isPreselected ? '' : 'disabled'}>
                    </td>
                `;
                tbody.appendChild(tr);

                const chk = tr.querySelector('.invoice-check');
                const allocInput = tr.querySelector('.alloc-input');
                const invIdInput = tr.querySelector('input[type="hidden"]');

                chk.addEventListener('change', function() {
                    if (this.checked) {
                        invIdInput.removeAttribute('disabled');
                        allocInput.removeAttribute('disabled');
                        if (!allocInput.value || parseFloat(allocInput.value) <= 0) {
                            const recAmount = parseFloat(document.getElementById('amount').value || 0);
                            const alreadyAlloc = getSumAllocatedExcept(idx);
                            const remainingToAlloc = Math.max(0, recAmount - alreadyAlloc);
                            allocInput.value = Math.min(inv.outstanding, remainingToAlloc || inv.outstanding).toFixed(2);
                        }
                    } else {
                        invIdInput.setAttribute('disabled', 'disabled');
                        allocInput.setAttribute('disabled', 'disabled');
                        allocInput.value = '';
                    }
                    recalcAllocation();
                });

                allocInput.addEventListener('input', function() {
                    const max = parseFloat(this.getAttribute('data-max') || 0);
                    if (parseFloat(this.value) > max) {
                        this.value = max.toFixed(2);
                    }
                    recalcAllocation();
                });
            });

            if (preselectedInvoiceId) {
                const totalAmountInput = document.getElementById('amount');
                if (!totalAmountInput.value) {
                    const preInv = data.invoices.find(i => String(i.id) === String(preselectedInvoiceId));
                    if (preInv) totalAmountInput.value = preInv.outstanding.toFixed(2);
                }
            }

            recalcAllocation();
        })
        .catch(err => {
            console.error('Error fetching invoices:', err);
            document.getElementById('invoicesBody').innerHTML = `
                <tr><td colspan="7" class="text-center text-danger py-4">Error loading invoices. Please try again.</td></tr>
            `;
        });
}

function getSumAllocatedExcept(skipIdx) {
    let sum = 0;
    document.querySelectorAll('.alloc-input:not([disabled])').forEach((input) => {
        const tr = input.closest('tr');
        const idx = tr.querySelector('.invoice-check').getAttribute('data-idx');
        if (String(idx) !== String(skipIdx)) {
            sum += parseFloat(input.value || 0);
        }
    });
    return sum;
}

function recalcAllocation() {
    let totalAlloc = 0;
    document.querySelectorAll('.alloc-input:not([disabled])').forEach(input => {
        totalAlloc += parseFloat(input.value || 0);
    });

    const recAmount = parseFloat(document.getElementById('amount')?.value || 0);
    const unallocated = Math.max(0, recAmount - totalAlloc);

    document.getElementById('lblTotalAllocated').innerText = '₹' + totalAlloc.toFixed(2);
    document.getElementById('lblUnallocated').innerText = '₹' + unallocated.toFixed(2);

    const btn = document.getElementById('saveReceiptBtn');
    if (totalAlloc > recAmount) {
        document.getElementById('lblTotalAllocated').classList.add('text-danger');
        btn.disabled = true;
    } else {
        document.getElementById('lblTotalAllocated').classList.remove('text-danger');
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const custSelect = document.getElementById('customer_id');
    custSelect.addEventListener('change', function() {
        loadCustomerInvoices(this.value);
    });

    document.getElementById('amount').addEventListener('input', recalcAllocation);

    if (custSelect.value) {
        loadCustomerInvoices(custSelect.value);
    }
});
</script>
@endpush
@endsection
