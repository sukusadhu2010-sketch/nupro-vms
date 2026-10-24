<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->type_label }} - {{ $invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 12mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11.5px;
            color: #222;
            background: #fff;
        }
        .invoice-box {
            max-width: 820px;
            margin: auto;
            border: 1px solid #333;
            padding: 18px;
        }
        .table-items th {
            background-color: #f1f3f5 !important;
            border: 1px solid #333 !important;
            padding: 5px 6px;
            font-size: 11px;
            text-transform: uppercase;
        }
        .table-items td {
            border: 1px solid #333 !important;
            padding: 5px 6px;
        }
        .table-summary td {
            border: 1px solid #333 !important;
            padding: 4px 6px;
        }
        .border-box {
            border: 1px solid #333;
        }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            .invoice-box { border: none; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center my-3">
        <button onclick="window.print()" class="btn btn-primary px-4 me-2">
            Print / Save as PDF
        </button>
        <button onclick="window.close()" class="btn btn-secondary px-3">
            Close
        </button>
    </div>

    <div class="invoice-box">
        <!-- Top Title Bar -->
        <div class="text-center border-bottom pb-2 mb-2">
            <h4 class="fw-bold mb-0 text-uppercase tracking-wider">{{ $invoice->type_label }}</h4>
            <small class="text-muted">(Original for Recipient / Tax Invoice under Section 31 of GST Act)</small>
        </div>

        <!-- Org / Supplier Info & Logo -->
        <div class="row align-items-center mb-3">
            <div class="col-8">
                <h5 class="fw-bold text-primary mb-1">{{ $org->name }}</h5>
                <div class="small">
                    {{ $org->address_line1 }} {{ $org->address_line2 }}<br>
                    {{ $org->city }}, {{ $org->state }} - {{ $org->pincode }}<br>
                    <strong>GSTIN:</strong> {{ $org->gstin }} | <strong>PAN:</strong> {{ substr($org->gstin ?? '', 2, 10) }}<br>
                    <strong>Email:</strong> {{ $org->email }} | <strong>Phone:</strong> {{ $org->contact_number }}
                </div>
            </div>
            <div class="col-4 text-end">
                @if ($org->logo_path)
                    <img src="{{ asset('storage/' . $org->logo_path) }}" alt="Logo" style="max-height: 70px; max-width: 170px;">
                @endif
            </div>
        </div>

        <!-- Invoice Details & Customer Info Grid -->
        <div class="border-box mb-3">
            <div class="row g-0">
                <div class="col-6 border-end p-2">
                    <strong class="text-uppercase text-decoration-underline d-block mb-1">Details of Receiver (Billed To):</strong>
                    <div class="fw-bold fs-6">{{ $invoice->customer?->name }}</div>
                    <div class="small" style="white-space: pre-line;">{{ $invoice->billing_address ?: $invoice->customer?->address }}</div>
                    <div class="small mt-1">
                        <strong>GSTIN:</strong> {{ $invoice->customer_gst ?: ($invoice->customer?->gstin ?? 'URP') }}<br>
                        <strong>State:</strong> {{ $invoice->customer?->state ?? '—' }}
                    </div>
                </div>
                <div class="col-6 p-2">
                    <table class="table table-sm table-borderless mb-0 small">
                        <tr>
                            <td class="fw-bold py-1" style="width: 45%;">Invoice No:</td>
                            <td class="py-1 fw-bold text-primary">{{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold py-1">Invoice Date:</td>
                            <td class="py-1">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold py-1">Customer PO No:</td>
                            <td class="py-1">{{ $invoice->po_number ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold py-1">Customer PO Date:</td>
                            <td class="py-1">{{ $invoice->po_date ? $invoice->po_date->format('d/m/Y') : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold py-1">SO Reference:</td>
                            <td class="py-1">{{ $invoice->salesOrder?->order_number ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table table-items mb-0">
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>Description of Goods / Services</th>
                    <th style="width: 75px;">HSN/SAC</th>
                    <th style="width: 55px;" class="text-end">Qty</th>
                    <th style="width: 45px;">Unit</th>
                    <th style="width: 75px;" class="text-end">Rate (₹)</th>
                    <th style="width: 60px;" class="text-end">Disc</th>
                    <th style="width: 50px;" class="text-end">GST %</th>
                    <th style="width: 85px;" class="text-end">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $idx => $item)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item->item_name }}</strong>
                            @if ($item->description)
                                <div class="text-muted small" style="font-size: 10px;">{{ $item->description }}</div>
                            @endif
                        </td>
                        <td class="text-center font-monospace">{{ $item->hsn_code ?: '—' }}</td>
                        <td class="text-end font-monospace">{{ number_format($item->quantity, 3) }}</td>
                        <td>{{ $item->unit }}</td>
                        <td class="text-end font-monospace">{{ number_format($item->rate, 2) }}</td>
                        <td class="text-end font-monospace">{{ $item->discount_amount > 0 ? number_format($item->discount_amount, 2) : '—' }}</td>
                        <td class="text-end">{{ $item->tax_percent }}%</td>
                        <td class="text-end fw-bold font-monospace">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Calculations & Tax Summary -->
        <div class="row g-0 border-box border-top-0 mb-3">
            <div class="col-7 border-end p-2 d-flex flex-column justify-content-between">
                <div>
                    <strong>Amount in Words:</strong>
                    <div class="fst-italic text-capitalize mt-1" style="font-size: 11px;">
                        INR {{ \App\Support\Amount::inWords($invoice->grand_total) }}
                    </div>
                </div>

                <div class="mt-3">
                    <strong>Bank Details for Payment:</strong>
                    <div class="small" style="white-space: pre-line;">{{ $org->bank_details ?: "Bank: State Bank of India\nA/C No: 123456789012\nIFSC: SBIN0001234" }}</div>
                </div>
            </div>
            <div class="col-5">
                <table class="table table-sm table-summary mb-0 small">
                    <tr>
                        <td>Sub Total:</td>
                        <td class="text-end font-monospace">₹{{ number_format($invoice->sub_total, 2) }}</td>
                    </tr>
                    @if ($invoice->discount_amount > 0)
                        <tr>
                            <td>Total Discount:</td>
                            <td class="text-end font-monospace">-₹{{ number_format($invoice->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Taxable Value:</td>
                        <td class="text-end font-monospace fw-bold">₹{{ number_format($invoice->taxable_amount, 2) }}</td>
                    </tr>
                    @if ($invoice->cgst > 0)
                        <tr>
                            <td>CGST:</td>
                            <td class="text-end font-monospace">₹{{ number_format($invoice->cgst, 2) }}</td>
                        </tr>
                    @endif
                    @if ($invoice->sgst > 0)
                        <tr>
                            <td>SGST:</td>
                            <td class="text-end font-monospace">₹{{ number_format($invoice->sgst, 2) }}</td>
                        </tr>
                    @endif
                    @if ($invoice->igst > 0)
                        <tr>
                            <td>IGST:</td>
                            <td class="text-end font-monospace">₹{{ number_format($invoice->igst, 2) }}</td>
                        </tr>
                    @endif
                    @if ($invoice->cess > 0)
                        <tr>
                            <td>CESS:</td>
                            <td class="text-end font-monospace">₹{{ number_format($invoice->cess, 2) }}</td>
                        </tr>
                    @endif
                    @if ($invoice->tcs > 0)
                        <tr>
                            <td>TCS:</td>
                            <td class="text-end font-monospace">₹{{ number_format($invoice->tcs, 2) }}</td>
                        </tr>
                    @endif
                    @if ($invoice->shipping_charge > 0)
                        <tr>
                            <td>Freight / Shipping:</td>
                            <td class="text-end font-monospace">₹{{ number_format($invoice->shipping_charge, 2) }}</td>
                        </tr>
                    @endif
                    @if ($invoice->round_off != 0)
                        <tr>
                            <td>Round Off:</td>
                            <td class="text-end font-monospace">₹{{ number_format($invoice->round_off, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="fw-bold table-light fs-6">
                        <td>Grand Total:</td>
                        <td class="text-end font-monospace text-primary">₹{{ number_format($invoice->grand_total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Terms & Signature -->
        <div class="row g-0 border-box p-2">
            <div class="col-8 pe-3">
                <strong class="small text-decoration-underline">Terms & Conditions:</strong>
                <div class="text-muted mt-1" style="font-size: 10px; white-space: pre-line;">{{ $invoice->terms ?: "1. Goods once sold will not be accepted back.\n2. Interest @ 18% p.a. will be charged if payment is delayed beyond due date.\n3. Subject to local jurisdiction only." }}</div>
            </div>
            <div class="col-4 text-center d-flex flex-column justify-content-between" style="min-height: 90px;">
                <div class="fw-bold small">For {{ $org->name }}</div>
                @if ($org->signature_path)
                    <div class="my-1">
                        <img src="{{ asset('storage/' . $org->signature_path) }}" alt="Sign" style="max-height: 45px;">
                    </div>
                @endif
                <div class="border-top pt-1 small text-muted">Authorised Signatory</div>
            </div>
        </div>
    </div>
</body>
</html>

