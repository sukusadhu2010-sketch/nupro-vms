<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Inventory Movement Ledger Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11px; color: #222; }
        .table th, .table td { padding: 4px 6px; font-size: 10.5px; border: 1px solid #333 !important; }
        .table th { background-color: #f1f3f5 !important; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print text-center my-3">
        <button onclick="window.print()" class="btn btn-primary px-4 me-2">Print Ledger</button>
        <button onclick="window.close()" class="btn btn-secondary px-3">Close</button>
    </div>

    <div class="container-fluid">
        <div class="text-center mb-3">
            <h4 class="fw-bold mb-0 text-uppercase">Inventory Movement Ledger</h4>
            <small class="text-muted">Generated on {{ date('d M Y, h:i A') }}</small>
        </div>

        <table class="table table-bordered mb-0">
            <thead>
                <tr>
                    <th style="width: 75px;">Date</th>
                    <th style="width: 65px;">Type</th>
                    <th>Reference No</th>
                    <th>PO No</th>
                    <th>Vendor</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th class="text-end" style="width: 65px;">Qty In</th>
                    <th class="text-end" style="width: 65px;">Qty Out</th>
                    <th class="text-end" style="width: 75px;">Balance</th>
                    <th style="width: 50px;">Unit</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ledgers as $l)
                    <tr>
                        <td>{{ $l->transaction_date->format('d/m/Y') }}</td>
                        <td class="text-uppercase fw-bold">{{ $l->transaction_type }}</td>
                        <td class="font-monospace">{{ $l->reference_no ?: '—' }}</td>
                        <td>{{ $l->purchaseOrder?->po_number ?? '—' }}</td>
                        <td>{{ $l->vendor?->name ?? '—' }}</td>
                        <td class="font-monospace">{{ $l->product?->sku ?? '—' }}</td>
                        <td>{{ $l->product?->name }}</td>
                        <td class="text-end font-monospace">{{ $l->qty_in > 0 ? '+' . number_format($l->qty_in, 3) : '—' }}</td>
                        <td class="text-end font-monospace">{{ $l->qty_out > 0 ? '-' . number_format($l->qty_out, 3) : '—' }}</td>
                        <td class="text-end font-monospace fw-bold">{{ number_format($l->balance_qty, 3) }}</td>
                        <td>{{ $l->unit }}</td>
                        <td>{{ $l->remarks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>

