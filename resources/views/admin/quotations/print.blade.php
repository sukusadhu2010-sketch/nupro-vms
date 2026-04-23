<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $quotation->quote_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
        }

        .quote-header {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 28px;
            font-weight: 700;
            color: #0d6efd;
        }

        .quote-title {
            font-size: 24px;
            font-weight: 600;
        }

        .info-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 14px;
            font-weight: 600;
        }

        .table th {
            background-color: #f8f9fa;
            font-size: 13px;
            text-transform: uppercase;
        }

        .total-row {
            background-color: #e7f1ff;
            font-weight: 700;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Print Button -->
        <div class="no-print mb-4 text-end">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-1"></i>Print / Save as PDF
            </button>
            <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>

        <!-- Header -->
        <div class="quote-header row">
            <div class="col-6">
                <div class="company-name">VMS Pro</div>
                <small class="text-muted">Vendor Management Solutions</small>
            </div>
            <div class="col-6 text-end">
                <div class="quote-title">QUOTATION</div>
                <div class="text-muted">{{ $quotation->quote_number }}</div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <div class="info-label">Quote To</div>
                    <div class="info-value">
                        {{ $quotation->enquiry->customer->company ?? $quotation->enquiry->customer->name }}</div>
                    <div class="text-muted small">{{ $quotation->enquiry->customer->user->email ?? '' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="info-label">Quote Number</div>
                        <div class="info-value">{{ $quotation->quote_number }}</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="info-label">Version</div>
                        <div class="info-value">V{{ $quotation->version }}</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="info-label">Date</div>
                        <div class="info-value">{{ $quotation->created_at->format('M d, Y') }}</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="info-label">Valid Until</div>
                        <div class="info-value">
                            {{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : 'N/A' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Status</div>
                        <div class="info-value">{{ ucfirst($quotation->status) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">#</th>
                    <th>Description</th>
                    <th class="text-center" style="width: 80px;">Qty</th>
                    <th class="text-end" style="width: 120px;">Unit Price</th>
                    <th class="text-end" style="width: 120px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->items as $i => $item)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>
                            <strong>{{ $item->product->name }}</strong>
                            @if ($item->notes)
                                <br><small class="text-muted">{{ $item->notes }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">${{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-end">Grand Total:</td>
                    <td class="text-end h5">${{ number_format($quotation->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if ($quotation->notes)
            <div class="mt-4">
                <div class="info-label mb-1">Terms & Conditions</div>
                <p class="small">{{ $quotation->notes }}</p>
            </div>
        @endif

        <div class="mt-5 pt-4 border-top text-center text-muted small">
            <p>This quotation is valid until
                {{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : 'further notice' }}.</p>
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>

</html>
