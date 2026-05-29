@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
                <h1 class="h2 mb-1">
                    <i class="fas fa-plus me-2 text-info"></i>Create Invoice
                </h1>
                <p class="text-muted">Generate invoice from sales order</p>
            </div>
        </div>

        <form method="POST" action="{{ route('invoices.store') }}">
            @csrf
            <div class="row g-4">
                <!-- Invoice Details -->
                <div class="col-lg-8">
                    <div class="card shadow-lg">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Invoice Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Sales Order <span class="text-danger">*</span></label>
                                    <select name="sales_order_id" id="salesOrderSelect" class="form-select" required>
                                        <option value="">Select Sales Order</option>
                                        @foreach ($salesOrders as $salesOrder)
                                            <option value="{{ $salesOrder->id }}"
                                                data-customer="{{ $salesOrder->customer->company }}"
                                                data-items="{{ json_encode(
                                                    $salesOrder->items->map(
                                                        fn($i) => [
                                                            'id' => $i->id,
                                                            'product' => $i->product->name ?? 'N/A',
                                                            'quantity' => $i->quantity,
                                                            'unit_price' => $i->unit_price,
                                                            'subtotal' => $i->subtotal,
                                                        ],
                                                    ),
                                                ) }}">
                                                {{ $salesOrder->order_number }} - {{ $salesOrder->customer->company }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('sales_order_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Invoice Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="invoice_date" class="form-control"
                                        value="{{ old('invoice_date', now()->toDateString()) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Due Date</label>
                                    <input type="date" name="due_date" class="form-control"
                                        value="{{ old('due_date', now()->addDays(30)->toDateString()) }}">
                                </div>
                            </div>

                            <!-- Customer Info -->
                            <div id="customerInfo" class="p-3 bg-light rounded mb-4" style="display: none;">
                                <h6 class="fw-bold mb-2">Customer Information</h6>
                                <div id="customerDetails"></div>
                            </div>

                            <!-- Invoice Items -->
                            <div id="invoiceItems" style="display: none;">
                                <label class="form-label fw-bold mb-3">Invoice Items</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="itemsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Product</th>
                                                <th style="width: 120px;">Quantity</th>
                                                <th style="width: 150px;">Unit Price</th>
                                                <th style="width: 150px;">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsBody">
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Subtotal:</th>
                                                <th class="text-end" id="subtotalDisplay">₹0.00</th>
                                            </tr>
                                            <tr>
                                                <th colspan="3" class="text-end">Tax (%):</th>
                                                <th>
                                                    <input type="number" name="tax_rate" id="taxRate"
                                                        class="form-control form-control-sm text-end" step="0.1"
                                                        value="0">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th colspan="3" class="text-end">Tax Amount:</th>
                                                <th class="text-end" id="taxAmountDisplay">₹0.00</th>
                                            </tr>
                                            <tr class="table-success">
                                                <th colspan="3" class="text-end">Total:</th>
                                                <th class="text-end text-success h5" id="totalDisplay">₹0.00</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mt-4">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes for this invoice...">{{ old('notes') }}</textarea>
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
                                <span>Total Items:</span>
                                <span id="totalItems">0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="h5 fw-bold">Total Amount:</span>
                                <span class="h4 text-success" id="summaryTotal">₹0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-info btn-lg w-100" id="submitBtn" disabled>
                            <i class="fas fa-save me-2"></i>Create Invoice
                        </button>
                        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const salesOrderSelect = document.getElementById('salesOrderSelect');
            const customerInfo = document.getElementById('customerInfo');
            const customerDetails = document.getElementById('customerDetails');
            const invoiceItems = document.getElementById('invoiceItems');
            const itemsBody = document.getElementById('itemsBody');
            const submitBtn = document.getElementById('submitBtn');

            salesOrderSelect.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];

                if (!option.value) {
                    customerInfo.style.display = 'none';
                    invoiceItems.style.display = 'none';
                    submitBtn.disabled = true;
                    return;
                }

                const customer = option.dataset.customer;
                const items = JSON.parse(option.dataset.items || '[]');

                // Show customer info
                customerDetails.innerHTML = `<p class="mb-0">${customer}</p>`;
                customerInfo.style.display = 'block';

                // Show invoice items
                renderItems(items);
                invoiceItems.style.display = 'block';
                submitBtn.disabled = false;

                if (items.length === 0) {
                    invoiceItems.style.display = 'none';
                    submitBtn.disabled = true;
                }
            });

            function renderItems(items) {
                itemsBody.innerHTML = '';
                let subtotal = 0;

                items.forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <input type="hidden" name="items[${index}][sales_order_item_id]" value="${item.id}">
                            <input type="hidden" name="items[${index}][product_id]" value="${item.product_id || ''}">
                            ${item.product}
                        </td>
                        <td>
                            <input type="number" name="items[${index}][quantity]" class="form-control qty-input" 
                                value="${item.quantity}" min="1" data-unit-price="${item.unit_price}" required>
                        </td>
                        <td>
                            <input type="number" name="items[${index}][unit_price]" class="form-control price-input" 
                                value="${item.unit_price}" step="0.01" min="0" required>
                        </td>
                        <td class="text-end item-subtotal">₹${parseFloat(item.subtotal).toFixed(2)}</td>
                    `;
                    itemsBody.appendChild(row);
                    subtotal += parseFloat(item.subtotal);
                });

                updateTotals();
            }

            function updateTotals() {
                const rows = itemsBody.querySelectorAll('tr');
                let subtotal = 0;
                let totalItems = 0;

                rows.forEach(row => {
                    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                    const unitPrice = parseFloat(row.querySelector('.price-input').value) || 0;
                    const itemSubtotal = qty * unitPrice;

                    row.querySelector('.item-subtotal').textContent = '₹' + itemSubtotal.toFixed(2);
                    subtotal += itemSubtotal;
                    totalItems += qty;
                });

                const taxRate = parseFloat(document.getElementById('taxRate').value) || 0;
                const taxAmount = subtotal * (taxRate / 100);
                const total = subtotal + taxAmount;

                document.getElementById('subtotalDisplay').textContent = '₹' + subtotal.toFixed(2);
                document.getElementById('taxAmountDisplay').textContent = '₹' + taxAmount.toFixed(2);
                document.getElementById('totalDisplay').textContent = '₹' + total.toFixed(2);
                document.getElementById('totalItems').textContent = totalItems;
                document.getElementById('summaryTotal').textContent = '₹' + total.toFixed(2);
            }

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input') || e.target
                    .id === 'taxRate') {
                    updateTotals();
                }
            });
        </script>
    @endpush
@endsection
