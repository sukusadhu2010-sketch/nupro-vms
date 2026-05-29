<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            background: #fff;
            padding: 20px;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 5px;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            text-align: right;
        }

        .invoice-number {
            font-size: 16px;
            color: #666;
            text-align: right;
        }

        .details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .bill-to,
        .invoice-details {
            flex: 1;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 8px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
        }

        .info-row {
            display: flex;
            margin-bottom: 4px;
        }

        .info-label {
            font-weight: bold;
            width: 120px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background: #f5f5f5;
            padding: 10px;
            text-align: left;
            border: 1px solid #333;
            font-weight: bold;
        }

        .items-table td {
            padding: 10px;
            border: 1px solid #333;
        }

        .items-table th.text-right,
        .items-table td.text-right {
            text-align: right;
        }

        .totals {
            width: 300px;
            margin-left: auto;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 10px;
            border-bottom: 1px solid #ccc;
        }

        .totals-row.total {
            background: #f5f5f5;
            font-weight: bold;
            font-size: 18px;
            border: 2px solid #333;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft {
            background: #6c757d;
            color: white;
        }

        .status-sent {
            background: #0d6efd;
            color: white;
        }

        .status-partial {
            background: #ffc107;
            color: black;
        }

        .status-paid {
            background: #198754;
            color: white;
        }

        .status-cancelled {
            background: #dc3545;
            color: white;
        }

        .notes {
            margin-top: 30px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        @media print {
            body {
                padding: 0;
            }

            .invoice-container {
                border: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="invoice-container">
        <div class="header">
            <div class="company-info">
                <div class="company-name">VMS Pro</div>
                <div>Vehicle Management System</div>
                <div>Invoice Management</div>
            </div>
            <div>
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <div style="margin-top: 10px;">
                    <span class="status-badge status-{{ $invoice->status }}">{{ $invoice->status_label }}</span>
                </div>
            </div>
        </div>

        <div class="details">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                <div class="company-name">{{ $invoice->customer->company }}</div>
                <div>{{ $invoice->customer->name }}</div>
                <div>{{ $invoice->customer->email }}</div>
                <div>{{ $invoice->customer->phone }}</div>
                @if ($invoice->customer->address)
                    <div>{{ $invoice->customer->address }}</div>
                @endif
            </div>
            <div class="invoice-details">
                <div class="section-title">Invoice Details</div>
                <div class="info-row">
                    <span class="info-label">Invoice Date:</span>
                    <span>{{ $invoice->invoice_date->format('d M Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Due Date:</span>
                    <span>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A' }}</span>
                </div>
                @if ($invoice->salesOrder)
                    <div class="info-row">
                        <span class="info-label">Sales Order:</span>
                        <span>{{ $invoice->salesOrder->order_number }}</span>
                    </div>
                @endif
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">₹{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>Subtotal:</span>
                <span>₹{{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Tax:</span>
                <span>₹{{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            <div class="totals-row total">
                <span>Total:</span>
                <span>₹{{ number_format($invoice->total_amount, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Paid:</span>
                <span>₹{{ number_format($invoice->paid_amount, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>Outstanding:</span>
                <span>₹{{ number_format($invoice->outstanding_amount, 2) }}</span>
            </div>
        </div>

        @if ($invoice->notes)
            <div class="notes">
                <div class="section-title">Notes</div>
                <div>{{ $invoice->notes }}</div>
            </div>
        @endif

        @if ($invoice->is_cancelled)
            <div class="notes" style="background: #fee; border-color: #fcc;">
                <div class="section-title" style="color: #c00;">Cancelled</div>
                <div>Reason: {{ $invoice->cancellation_reason }}</div>
                <div>Cancelled by: {{ $invoice->canceller->name ?? 'Unknown' }} on
                    {{ $invoice->cancelled_at->format('d M Y H:i') }}</div>
            </div>
        @endif

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Generated on {{ now()->format('d M Y H:i:s') }}</p>
        </div>
    </div>
</body>

</html>
