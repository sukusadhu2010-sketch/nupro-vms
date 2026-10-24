<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Customer Receipt Voucher - {{ $receipt->payment_number }}</title>
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
        <button onclick="window.print()" class="btn btn-primary px-4 me-2">Print Receipt</button>
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
                <h5 class="text-uppercase fw-bold text-secondary">Receipt Voucher</h5>
                <div class="badge bg-light text-dark border fs-6 font-monospace">{{ $receipt->payment_number }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <strong class="d-block text-muted small">Received With Thanks From:</strong>
                <h5 class="fw-bold text-dark mt-1">{{ $receipt->customer?->name }}</h5>
                <div class="small text-muted">{{ $receipt->customer?->address }}</div>
                <div class="small mt-1"><strong>GSTIN:</strong> {{ $receipt->customer?->gstin ?: 'URP' }}</div>
            </div>
            <div class="col-6 text-end">
                <table class="table table-sm table-borderless small ms-auto" style="max-width: 280px;">
                    <tr>
                        <td class="text-muted">Receipt Date:</td>
                        <td class="fw-bold">{{ $receipt->payment_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Payment Mode:</td>
                        <td class="fw-bold">{{ $receipt->mode_label }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Reference / Cheque #:</td>
                        <td class="fw-bold font-monospace">{{ $receipt->reference_number ?: '—' }}</td>
                    </tr>
                    @if ($receipt->bank_name)
                    <tr>
                        <td class="text-muted">Bank Name:</td>
                        <td>{{ $receipt->bank_name }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="p-3 bg-light border rounded mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fs-6 fw-semibold">Amount Received:</span>
                <span class="fs-4 fw-bold text-success font-monospace">₹{{ number_format($receipt->amount, 2) }}</span>
            </div>
            <div class="small fst-italic text-capitalize mt-2 border-top pt-2">
                <strong>Amount in Words:</strong> INR {{ \App\Support\Amount::inWords($receipt->amount) }}
            </div>
        </div>

        <h6 class="fw-bold text-dark mb-2">Invoice Settlement Allocation</h6>
        <table class="table table-sm table-bordered mb-4">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Invoice Number</th>
                    <th>Invoice Date</th>
                    <th class="text-end">Invoice Amount</th>
                    <th class="text-end">Amount Allocated</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($receipt->allocations as $idx => $alloc)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="font-monospace fw-semibold">{{ $alloc->invoice?->invoice_number }}</td>
                        <td>{{ $alloc->invoice?->invoice_date->format('d/m/Y') }}</td>
                        <td class="text-end font-monospace">₹{{ number_format($alloc->invoice?->grand_total, 2) }}</td>
                        <td class="text-end font-monospace fw-bold">₹{{ number_format($alloc->allocated_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-2 text-muted">Direct / Unallocated Advance payment</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($receipt->remarks)
            <div class="small mb-4">
                <strong>Remarks:</strong> {{ $receipt->remarks }}
            </div>
        @endif

        <div class="row pt-5 mt-4">
            <div class="col-6">
                <div class="border-top pt-2 small text-muted" style="width: 200px;">
                    Receiver's Signature
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

