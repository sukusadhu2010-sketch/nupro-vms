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

        .section-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .kv-label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
        }

        .kv-value {
            font-size: 13px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 4px 8px;
            background-color: #f8f9fa;
        }

        .desc-entry {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 12px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
            }

            @page {
                size: A4;
                margin: 12mm;
            }

            .table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
            }
        }

        /* Seal & signature positioned bottom-right, letter head full-width top */
        .print-seal {
            position: fixed;
            bottom: 28mm;
            right: 20mm;
            width: 32mm;
            opacity: 0.9;
            z-index: 50;
        }

        .print-signature {
            position: fixed;
            bottom: 20mm;
            right: 55mm;
            width: 38mm;
            opacity: 0.95;
            z-index: 50;
        }

        .print-letterhead {
            display: block;
            width: 100%;
            margin-bottom: 10px;
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

        @php($org = \App\Models\OrganizationSetting::current())

        <!-- Letter Head (full width, above the quotation header) -->
        @if ($org->letterhead_path)
            <img src="{{ asset($org->letterhead_url) }}" alt="Letter Head" class="print-letterhead">
        @endif

        <!-- Seal (bottom-right, like a physical stamp) -->
       

        <!-- Digital Signature (bottom-right, near signatory block) -->
        @if ($org->signature_path)
            <img src="{{ asset($org->signature_url) }}" alt="Digital Signature" class="print-signature">
        @endif
        <div class="quote-header row">
            <div class="col-6">
                {{-- @if ($org->logo_path)
                    <img src="{{ asset('storage/' . $org->logo_path) }}" alt="{{ $org->name }}" style="max-height: 80px; max-width: 240px;">
                @endif --}}
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
                    <!-- <div class="col-6 mb-3">
                        <div class="info-label">Version</div>
                        <div class="info-value">V{{ $quotation->version }}</div>
                    </div> -->
                    <div class="col-6 mb-3">
                        <div class="info-label">Date</div>
                        <div class="info-value">{{ $quotation->created_at->format('M d, Y') }}</div>
                    </div>
                    <!-- <div class="col-6 mb-3">
                        <div class="info-label">Valid Until</div>
                        <div class="info-value">
                            {{ $quotation->valid_until ? $quotation->valid_until->format('M d, Y') : 'N/A' }}</div>
                    </div> -->
                    <!-- <div class="col-6">
                        <div class="info-label">Status</div>
                        <div class="info-value">{{ ucfirst($quotation->status) }}</div>
                    </div> -->
                </div>
            </div>
        </div>

        @if ($quotation->acknowledgement || $quotation->customer_information || $quotation->kind_attention || $quotation->product_spec)
            <div class="mt-4">
                <!-- <div class="info-label mb-1">Acknowledgement &amp; Product Specification</div> -->
               
                @if ($quotation->customer_information)
                    <div class="mb-2">
                        <!-- <div class="kv-label">Customer Information</div> -->
                        <div class="kv-value small" style="white-space: pre-line;">{{ $quotation->customer_information }}</div>
                    </div>
                @endif
                @if ($quotation->kind_attention)
                    <div class="mb-2">
                        <!-- <div class="kv-label">Kind Atten.</div> -->
                        <div class="kv-value small">{{ $quotation->kind_attention }}</div>
                    </div>
                @endif
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
                    <th class="text-center" style="width: 80px;">Qty (NOS)</th>
                    <th class="text-end" style="width: 120px;">Unit Price (Each No.)</th>
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
                        <div class="desc-entry">
                            @if (!empty($item->payment_methods) && count($item->payment_methods))
                                <div class="mb-1">
                                    <strong>Payment Methods:</strong> {{ implode(', ', $item->payment_methods) }}
                                </div>
                            @endif
                            <div>
                                <strong>Description:</strong>
                                <span><strong>MOC:</strong> {{ $item->moc }}</span> &nbsp;|&nbsp;
                                <span><strong>MFG Spec:</strong> {{ $item->mfg_spec }}</span> &nbsp;|&nbsp;
                                <span><strong>Trim:</strong> {{ $item->trim }}</span> &nbsp;|&nbsp;
                                <span><strong>Operation:</strong> {{ $item->operation }}</span> &nbsp;|&nbsp;
                                <span><strong>End Connection:</strong> {{ $item->end_connection }}</span> &nbsp;|&nbsp;
                                <span><strong>Rating:</strong> {{ $item->rating }}</span> &nbsp;|&nbsp;
                                <span><strong>Media:</strong> {{ $item->media }}</span>
                            </div>
                            @if ($item->remarks || $item->notes)
                                <div class="mt-1"><strong>Remarks:</strong> {{ $item->remarks ?? $item->notes }}</div>
                            @endif
                        </div>
                    </td></tr>
                @endforeach
            </tbody>
            <tfoot>
                @if (($quotation->tax_amount ?? 0) > 0)
                    <tr>
                        <td colspan="4" class="text-end">Total Tax</td>
                        <td class="text-end">₹{{ number_format($quotation->tax_amount, 2) }}</td>
                    </tr>
                @endif
                @if ($quotation->tax_type === 'igst' && (float) ($quotation->igst ?? 0) > 0)
                    <tr>
                        <td colspan="4" class="text-end">IGST @18%:</td>
                        <td class="text-end">₹{{ number_format((float) $quotation->igst, 2) }}</td>
                    </tr>
                @endif
                @if ($quotation->tax_type === 'sgst_cgst')
                    @if ((float) ($quotation->sgst ?? 0) > 0)
                        <tr>
                            <td colspan="4" class="text-end">SGST @9%:</td>
                            <td class="text-end">₹{{ number_format((float) $quotation->sgst, 2) }}</td>
                        </tr>
                    @endif
                    @if ((float) ($quotation->cgst ?? 0) > 0)
                        <tr>
                            <td colspan="4" class="text-end">CGST @9%:</td>
                            <td class="text-end">₹{{ number_format((float) $quotation->cgst, 2) }}</td>
                        </tr>
                    @endif
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
                    @if ($quotation->prices_basis)
                        <tr>
                            <td><strong>Prices Basis</strong></td>
                            <td>
                                {{ $quotation->prices_basis }}
                                @if ($quotation->prices_basis_option)
                                     <em>{{ $quotation->prices_basis_option === 'ex-works' ? 'Ex-Works' : 'Transported Godown' }}</em>
                                @endif
                            </td>
                        </tr>
                    @endif
                    @if ($quotation->payment_term_option || $quotation->payment_terms)
                        <tr>
                            <td><strong>Payment Terms</strong></td>
                            <td>
                                <table class="table table-sm table-borderless mb-1" style="font-size:12px;">
                                    @foreach (['LC / Credit' => 'lc_credit', 'Advance + PI' => 'advance_pi', 'PDC' => 'pdc', 'Proforma Invoice' => 'proforma_invoice'] as $label => $key)
                                        <tr>
                                            <td style="width:24px;">{{ $quotation->payment_term_option === $key ? '' : '' }}</td>
                                            <td style="width:160px;">{{ $label }}</td>
                                            <td>{{ ($quotation->payment_term_text[$key] ?? '') ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                                @if ($quotation->payment_terms)
                                    {!! nl2br(e($quotation->payment_terms)) !!}
                                @endif
                            </td>
                        </tr>
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
                 @if ($org->seal_path)
            <img src="{{ asset($org->seal_url) }}" alt="Seal" class="print-seal">
        @endif
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
