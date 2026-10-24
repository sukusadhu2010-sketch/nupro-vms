<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Vendor Payment Voucher - {{ $vendorPayment->payment_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; color: #222; }
        .voucher-box { max-width: 800px; margin: auto; border: 2px solid #333; padding: 20px; }
        @media print { .no-print { display: none !important; } .voucher-box { border: 1px solid #333; } }
    </style>
</head>
<body>
    <div class="no-print text-center my-3">
        <button onclick="window.print()" class="btn btn-primary px-4 me-2">Print Voucher</button>
        <button onclick="window.close()" class="btn btn-secondary px-3">Close</button>
    </div>

    <div class="voucher-box">
        <div class="row align-items-center mb-3 border-bottom pb-3">
            <div class="col-8">
                <h4 class="fw-bold text-primary mb-1">{{ $org->name }}</h4>
                <div class="small">
                    {{ $org->address_line1 }} {{ $org->address_line2 }}<br>
                    {{ $org->city }}, {{ $org->state }} - {{ $org->pincode }}<br>
                    <strong>GSTIN:</strong> {{ $org->gstin }} | <strong>Phone:</strong> {{ $org->contact_number }}
                </div>
            </div>
            <div class="col-4 text-end">
                <h5 class="text-uppercase fw-bold text-danger">Payment Voucher</h5>
                <div class="badge bg-light text-dark border fs-6 font-monospace">{{ $vendorPayment->payment_number }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <strong class="d-block text-muted small">Paid To Vendor:</strong>
                <h5 class="fw-bold text-dark mt-1">{{ $vendorPayment->vendor?->name }}</h5>
                <div class="small text-muted">{{ $vendorPayment->vendor?->address }}</div>
                <div class="small mt-1"><strong>GSTIN:</strong> {{ $vendorPayment->vendor?->gst_no ?: '—' }}</div>
            </div>
            <div class="col-6 text-end">
                <table class="table table-sm table-borderless small ms-auto" style="max-width: 280px;">
                    <tr>
                        <td class="text-muted">Payment Date:</td>
                        <td class="fw-bold">{{ $vendorPayment->payment_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Payment Mode:</td>
                        <td class="fw-bold">{{ $vendorPayment->mode_label }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Reference / Cheque #:</td>
                        <td class="fw-bold font-monospace">{{ $vendorPayment->reference_number ?: '—' }}</td>
                    </tr>
                    @if ($vendorPayment->purchaseOrder)
                    <tr>
                        <td class="text-muted">Linked PO #:</td>
                        <td class="fw-bold font-monospace">{{ $vendorPayment->purchaseOrder->po_number }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="p-3 bg-light border rounded mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fs-6 fw-semibold">Amount Paid:</span>
                <span class="fs-4 fw-bold text-danger font-monospace">₹{{ number_format($vendorPayment->amount, 2) }}</span>
            </div>
            <div class="small fst-italic text-capitalize mt-2 border-top pt-2">
                <strong>Amount in Words:</strong> INR {{ \App\Support\Amount::inWords($vendorPayment->amount) }}
            </div>
        </div>

        @if ($vendorPayment->remarks)
            <div class="small mb-4 border p-2 rounded">
                <strong>Particulars / Remarks:</strong><br>
                {{ $vendorPayment->remarks }}
            </div>
        @endif

        <div class="row pt-5 mt-4">
            <div class="col-6">
                <div class="border-top pt-2 small text-muted" style="width: 200px;">
                    Payee Signature / Acknowledgement
                </div>
            </div>
            <div class="col-6 text-end">
                <div class="border-top pt-2 small text-muted ms-auto" style="width: 200px;">
                    For {{ $org->name }}<br>(Authorised Signatory)
                </div>
            </div>
        </div>
    </div>
</body>
</html>

