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
        @php($org = \App\Models\OrganizationSetting::current())
        <div class="quote-header row">
            <div class="col-6">
                @if ($org->logo_path)
                    <img src="{{ asset('storage/' . $org->logo_path) }}" alt="{{ $org->name }}" style="max-height: 80px; max-width: 240px;">
                @endif
                <div class="company-name">{{ $org->name }}</div>
                <small class="text-muted">Vendor Management Solutions</small>
                @if ($org->address_line1 || $org->city)
                    <div class="small text-muted mt-1">
                        {{ $org->address_line1 }}@if($org->address_line2), {{ $org->address_line2 }}@endif<br>
                        {{ $org->city }}@if($org->state), {{ $org->state }}@endif @if($org->pincode)- {{ $org->pincode }}@endif<br>
                        @if($org->contact_number)Ph: {{ $org->contact_number }}@endif
                        @if($org->gstin) &nbsp;|&nbsp; GSTIN: {{ $org->gstin }}@endif
                    </div>
                @endif
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
                        {{  $quotation->enquiry->customer->name }}</div>
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

          @if ($quotation->acknowledgement || $quotation->product_spec)
            <div class="mt-4">
                <div class="info-label mb-1">Acknowledgement &amp; Product Specification</div>
                @if ($quotation->acknowledgement)
                    <p class="small mb-1">{{ $quotation->acknowledgement }}</p>
                @endif
                @if ($quotation->product_spec)
                    <p class="small mb-0"><strong>Product Specification / Type:</strong> {{ $quotation->product_spec }}</p>
                @endif
            </div>
        @endif

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

                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">₹{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    <tr><td colspan="5">
                         @if ($item->notes)
                                <br><small class="text-muted">{{ $item->notes }}</small>
                            @endif
                            <div class="mt-1 small">
                                <strong>Description:</strong>
                                <span><strong>MOC:</strong> {{ $item->moc }}</span> &nbsp;|&nbsp;
                                <span><strong>MFG Spec:</strong> {{ $item->mfg_spec }}</span> &nbsp;|&nbsp;
                                <span><strong>Trim:</strong> {{ $item->trim }}</span> &nbsp;|&nbsp;
                                <span><strong>Operation:</strong> {{ $item->operation }}</span> &nbsp;|&nbsp;
                                <span><strong>End Connection:</strong> {{ $item->end_connection }}</span> &nbsp;|&nbsp;
                                <span><strong>Rating:</strong> {{ $item->rating }}</span> &nbsp;|&nbsp;
                                <span><strong>Media:</strong> {{ $item->media }}</span>
                                @if ($item->remarks)
                                    <br><strong>Remarks:</strong> {{ $item->remarks }}
                                @endif
                            </div>
                    </td></tr>
                @endforeach
            </tbody>
            <tfoot>
                @if (($quotation->tax_amount ?? 0) > 0)
                    <tr>
                        <td colspan="4" class="text-end">Tax
                            @if ($quotation->tax_type === 'igst') (IGST @ {{ $quotation->igst }}%)
                            @else (CGST @ {{ $quotation->cgst }}% + SGST @ {{ $quotation->sgst }}%)
                            @endif</td>
                        <td class="text-end">₹{{ number_format($quotation->tax_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td colspan="4" class="text-end">Subtotal (Quantity Amount):</td>
                    <td class="text-end">₹{{ number_format($quotation->total_amount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="4" class="text-end h5">Grand Total:</td>
                    <td class="text-end h5">₹{{ number_format($quotation->total_amount + ($quotation->tax_amount ?? 0), 2) }}</td>
                </tr>
                @if ($quotation->amount_in_words)
                    <tr>
                        <td colspan="5" class="fst-italic">
                            <small><strong>Amount in Words:</strong> {{ $quotation->amount_in_words }}</small>
                        </td>
                    </tr>
                @endif
            </tfoot>
        </table>

      

        @if ($quotation->delivery_terms || $quotation->warranty_terms || $quotation->payment_terms || $quotation->inspection_vendor_scope || $quotation->inspection_third_party_scope)
            <div class="mt-4">
                <div class="info-label mb-1">Terms &amp; Conditions</div>
                <table class="table table-sm table-borderless small mb-0">
                    @if ($quotation->delivery_terms)
                        <tr><td style="width:220px"><strong>Delivery Terms</strong></td><td>{{ $quotation->delivery_terms }}</td></tr>
                    @endif
                    @if ($quotation->warranty_terms)
                        <tr><td><strong>Warranty Terms</strong></td><td>{{ $quotation->warranty_terms }}</td></tr>
                    @endif
                    @if ($quotation->payment_terms)
                        <tr><td><strong>Payment Terms</strong></td><td>{!! nl2br(e($quotation->payment_terms)) !!}</td></tr>
                    @endif
                    @if ($quotation->inspection_vendor_scope)
                        <tr><td><strong>Inspection — Vendor Scope</strong></td><td>{!! nl2br(e($quotation->inspection_vendor_scope)) !!}</td></tr>
                    @endif
                    @if ($quotation->inspection_third_party_scope)
                        <tr><td><strong>Inspection — Third Party</strong></td><td>{!! nl2br(e($quotation->inspection_third_party_scope)) !!}</td></tr>
                    @endif
                </table>
            </div>
        @endif

        @if ($quotation->notes || $quotation->closing_statement || $quotation->signatory_company || $quotation->signatory_designation)
            <div class="mt-4">
                <div class="info-label mb-1">Notes &amp; Signatory</div>
                @if ($quotation->notes)
                    <div class="small mb-2">{!! \App\Support\HtmlSanitizer::clean($quotation->notes) !!}</div>
                @endif
                @if ($quotation->closing_statement)
                    <p class="small fst-italic mb-2">{{ $quotation->closing_statement }}</p>
                @endif
                @if ($quotation->signatory_company || $quotation->signatory_designation)
                    <div class="border-top pt-2 small">
                        <strong>{{ $quotation->signatory_company }}</strong>
                        @if ($quotation->signatory_designation)<div>{{ $quotation->signatory_designation }}</div>@endif
                    </div>
                @endif
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
