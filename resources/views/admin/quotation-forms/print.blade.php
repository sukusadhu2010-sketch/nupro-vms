<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $form->ref_no }} - {{ $form->title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #111; margin: 24px; }
        .doc-header { text-align: center; border-bottom: 3px double #333; padding-bottom: 10px; }
        .doc-header h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .doc-header .cert { font-size: 12px; font-style: italic; }
        .meta-row { display: flex; justify-content: space-between; margin-top: 12px; font-weight: bold; }
        .doc-title { text-align: center; text-decoration: underline; font-weight: bold; font-size: 15px; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 5px 7px; text-align: left; vertical-align: top; }
        th { background: #eee; }
        .num { text-align: right; }
        .section-label { font-weight: bold; margin-top: 12px; }
        .sign-block { margin-top: 48px; text-align: right; }
        .sign-line { border-top: 1px solid #333; display: inline-block; padding-top: 4px; min-width: 260px; text-align: center; }
        .notes { white-space: pre-line; }
        .print-seal { position: fixed; bottom: 28mm; right: 20mm; width: 32mm; opacity: 0.9; z-index: 50; }
        .print-signature { position: fixed; bottom: 20mm; right: 55mm; width: 38mm; opacity: 0.95; z-index: 50; }
        .print-letterhead { display: block; width: 100%; margin-bottom: 10px; }
        @media print {
            body { margin: 10mm; }
            .no-print { display: none !important; }
        }
        .no-print { margin: 16px 0; }
        .no-print a, .no-print button { padding: 8px 14px; margin-right: 8px; border: 1px solid #333; background: #f4f4f4; cursor: pointer; text-decoration: none; color: #111; font-size: 13px; }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="{{ route('quotation-forms.edit', $form) }}">Edit</a>
        <a href="{{ route('quotation-forms.index') }}">Back to List</a>
        <button onclick="window.print()">Print / Export PDF</button>
    </div>

    @php($org = \App\Models\OrganizationSetting::current())

    <!-- Letter Head / Seal / Digital Signature -->
    @if ($org->letterhead_path)
        <img src="{{ $org->letterhead_url }}" alt="Letter Head" class="print-letterhead">
    @endif
    @if ($org->seal_path)
        <img src="{{ $org->seal_url }}" alt="Seal" class="print-seal">
    @endif
    @if ($org->signature_path)
        <img src="{{ $org->signature_url }}" alt="Digital Signature" class="print-signature">
    @endif

    <!-- HEADER -->
    <div class="doc-header">
        <h1>{{ $form->company_name }}</h1>
        @if ($form->certification)
            <div class="cert">{{ $form->certification }}</div>
        @endif
    </div>
    <div class="meta-row">
        <span>Ref. No.: {{ $form->ref_no }}</span>
        <span>Date: {{ $form->form_date?->format('d-m-Y') }}</span>
    </div>
    <div class="doc-title">{{ $form->title }}</div>

    <!-- RECIPIENT -->
    <table>
        <tr>
            <th style="width:18%">To</th>
            <td>
                <strong>{{ $form->to_company }}</strong><br>
                {!! nl2br(e($form->to_address ?? '')) !!}
                @if ($form->project_details)
                    <br><strong>Project:</strong> {{ $form->project_details }}
                @endif
            </td>
        </tr>
        <tr>
            <th>Contact Person</th>
            <td>{{ $form->contact_person }}@if($form->contact_designation), {{ $form->contact_designation }}@endif</td>
        </tr>
    </table>

    <!-- ACKNOWLEDGEMENT + SPEC -->
    @if ($form->acknowledgement)
        <p class="notes">{{ $form->acknowledgement }}</p>
    @endif
    <div class="section-label">Product Specification / Type: {{ $form->product_spec }}</div>

    <!-- DESCRIPTION -->
    <table>
        <thead>
            <tr>
                <th style="width:3%">#</th>
                <th>* MOC</th>
                <th>* MFG Spec</th>
                <th>* Trim</th>
                <th>* Operation</th>
                <th>* End Connection</th>
                <th>* Rating</th>
                <th>* Media</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($form->description_entries ?? [] as $i => $d)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $d['moc'] ?? '' }}</td>
                    <td>{{ $d['mfg_spec'] ?? '' }}</td>
                    <td>{{ $d['trim'] ?? '' }}</td>
                    <td>{{ $d['operation'] ?? '' }}</td>
                    <td>{{ $d['end_connection'] ?? '' }}</td>
                    <td>{{ $d['rating'] ?? '' }}</td>
                    <td>{{ $d['media'] ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="8">No description entries.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- ITEM TABLE -->
    <table>
        <thead>
            <tr>
                <th style="width:6%">#</th>
                <th style="width:22%">Size (MM)</th>
                <th style="width:15%">Quantity (Nos)</th>
                <th style="width:20%">Unit Price (Each No.)</th>
                <th style="width:20%">Total Price</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($form->items ?? [] as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item['size_mm'] ?? '' }}</td>
                    <td class="num">{{ $item['quantity'] ?? '' }}</td>
                    <td class="num">{{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                    <td class="num">{{ number_format($item['total_price'] ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No items.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="num">Grand Total</th>
                <th class="num">{{ number_format($form->grand_total, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- TERMS -->
    <div class="section-label">Terms &amp; Conditions</div>
    <table>
        <tr><th style="width:30%">Delivery Terms</th><td>{{ $form->delivery_terms }}</td></tr>
        <tr>
            <th>Tax Details</th>
            <td>
                @php
                    $taxParts = [];
                    if (($form->igst_percent ?? 0) > 0) { $taxParts[] = 'IGST: ' . rtrim(rtrim(number_format((float) $form->igst_percent, 2), '0'), '.') . '%'; }
                    if (($form->sgst_percent ?? 0) > 0) { $taxParts[] = 'SGST: ' . rtrim(rtrim(number_format((float) $form->sgst_percent, 2), '0'), '.') . '%'; }
                    if (($form->cgst_percent ?? 0) > 0) { $taxParts[] = 'CGST: ' . rtrim(rtrim(number_format((float) $form->cgst_percent, 2), '0'), '.') . '%'; }
                @endphp
                {{ implode(' | ', $taxParts) ?: 'N/A' }}
            </td>
        </tr>
        <tr><th>Payment Terms</th><td class="notes">{{ $form->payment_terms }}</td></tr>
        <tr><th>Inspection — Vendor Scope</th><td class="notes">{{ $form->inspection_vendor_scope }}</td></tr>
        <tr><th>Inspection — Third Party Scope</th><td class="notes">{{ $form->inspection_third_party_scope }}</td></tr>
        <tr><th>Warranty</th><td>{{ $form->warranty_terms }}</td></tr>
    </table>

    <!-- NOTES + CLOSING -->
    @if ($form->notes)
        <div class="section-label">Notes</div>
        <p class="notes">{{ $form->notes }}</p>
    @endif

    <p><strong>{{ $form->closing_statement }}</strong></p>

    <!-- SIGNATORY -->
    <div class="sign-block">
        <div class="sign-line">
            <strong>For {{ $form->signatory_company }}</strong><br>
            (Authorized Signatory)<br>
            {{ $form->signatory_designation }}
        </div>
    </div>
</body>
</html>
