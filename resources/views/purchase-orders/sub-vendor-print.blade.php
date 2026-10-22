{{-- ============================================================
     Sub Vendor Purchase Order - Print / PDF Template (A4 Portrait)
     DOMPDF compatible. Used when the PO vendor_type = 'SUB VENDOR'.
     Usage:
       return PDF::loadView('purchase-orders.sub-vendor-print', compact('procurement'))->stream('PO.pdf');
     or render as HTML for browser print preview.
     ============================================================ --}}

@php
$org = \App\Models\OrganizationSetting::current();
$vendor = $procurement->vendor;
$salesOrder = $procurement->salesOrder ?? $procurement->salesOrders->first();
$items = $procurement->items()->with(['product', 'salesOrderItem.product'])->get();

// --- Absolute paths for DOMPDF (it cannot read URLs) ---
$assetPath = function (?string $storagePath): ?string {
if (! $storagePath) return null;
return \Storage::disk('public')->path($storagePath);
};
$logoPath = ($org->logo_path) ?  url('/').'/storage/'.$org->logo_path : null;
$signaturePath = ($org->signature_path) ? url('/').'/storage/'.$org->signature_path : null;
$sealPath = ($org->seal_path) ? url('/').'/storage/'.$org->seal_path : null;

// --- Formatters ---
$fmtDate = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('d-m-Y') : '-';
$fmtQty = fn ($q) => rtrim(rtrim(number_format((float) $q, 2, '.', ''), '0'), '.') ?: '0';
$fmtAmt = fn ($a) => number_format((float) $a, 2);

// Clean helper: drop nulls / placeholders / junk values
        $clean = function ($v): ?string {
        if ($v === null) return null;
        $v = trim((string) $v);
        $v = preg_replace('/^\s*(\xc2\xa0|\xe2\x80\x8b)+/u', '', $v) ?? '';
if ($v === '' || strtolower($v) === 'null' || strtolower($v) === 'na' || strtolower($v) === 'n/a' || strtolower($v) === '-') return null;
if (mb_strlen(preg_replace('/[^A-Za-z0-9]/', '', $v) ?? '') < 2) return null;
    return $v;
    };

    // --- Amount to words (Indian numbering) ---
    $amountInWords=function (float $amount): string {
    $amount=round($amount, 2);
    $rupees=(int) floor($amount);
    $paise=(int) round(($amount - $rupees) * 100);

    $words=[0=> '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
    6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
    11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
    16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
    20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
    60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'];

    $convert = function (int $n) use (&$convert, $words): string {
    if ($n < 20) return $words[$n];
        if ($n < 100) {
        $t=intdiv($n, 10) * 10; $r=$n % 10;
        return trim($words[$t] . ($r ? ' ' . $convert($r) : '' ));
        }
        if ($n < 1000) {
        $h=intdiv($n, 100); $r=$n % 100;
        return trim($words[$h] . ' Hundred' . ($r ? ' ' . $convert($r) : '' ));
        }
        if ($n < 100000) {
        $t=intdiv($n, 1000); $r=$n % 1000;
        return trim($convert($t) . ' Thousand' . ($r ? ' ' . $convert($r) : '' ));
        }
        if ($n < 10000000) {
        $l=intdiv($n, 100000); $r=$n % 100000;
        return trim($convert($l) . ' Lakh' . ($r ? ' ' . $convert($r) : '' ));
        }
        $c=intdiv($n, 10000000); $r=$n % 10000000;
        return trim($convert($c) . ' Crore' . ($r ? ' ' . $convert($r) : '' ));
        };

        $txt='Rupees ' . ($rupees> 0 ? $convert($rupees) : 'Zero') . ' Only';
        if ($paise > 0) $txt .= ' and ' . $convert($paise) . ' Paise Only';
        return $txt;
        };

        // --- Product technical info resolver (Product Master + SO item + PO notes) ---
        $specFields = [
        'drawing_no' => 'Drawing No', 'revision_no' => 'Revision', 'part_no' => 'Part No',
        'material_grade' => 'Material', 'valve_size' => 'Size', 'thickness' => 'Thickness',
        'moc' => 'MOC', 'customer_part_no' => 'Customer Part No',
        'casting_type' => 'Casting Type', 'pressure_class' => 'Pressure Class',
        'end_connection' => 'End Connection', 'testing_standard' => 'Testing Standard',
        'casting_weight' => 'Casting Weight', 'heat_treatment' => 'Heat Treatment',
        'machining_allowance' => 'Machining Allowance', 'pattern_no' => 'Pattern No',
        'mfg_spec' => 'MFG Spec', 'trim' => 'Trim', 'rating' => 'Rating', 'media' => 'Media',
        ];
        $getSpecs = function ($item) use ($specFields, $clean): array {
        $src = [];
        foreach ([$item->salesOrderItem, $item->product] as $obj) {
        if (! $obj) continue;
        foreach ($specFields as $f => $label) {
        $v = $clean($obj->{$f} ?? null);
        if ($v !== null && ! isset($src[$label])) $src[$label] = $v;
        }
        }
        if ($r = $clean($item->notes)) $src['Remarks'] = $r;
        elseif ($r = $clean($item->salesOrderItem->product->description ?? $item->product->description ?? null)) $src['Description'] = $r;
        return $src;
        };

        // --- Totals & GST (dynamic) ---
        $taxable = $items->sum(fn ($i) => (float) $i->unit_price * $i->quantity);
        $cgstRate = $sgstRate = 0.09; // 9% + 9%
        $igstRate = 0.18;
        // If PO stores an explicit tax rate, use it; otherwise default CGST+SGST 9%+9%
        $poTaxRate = $clean($procurement->tax_rate ?? null) !== null ? ((float) $procurement->tax_rate) / 100 : null;
        if ($poTaxRate !== null) {
        $cgstRate = $sgstRate = $poTaxRate / 2;
        $igstRate = $poTaxRate;
        }
        // Intra-state (West Bengal) default: CGST + SGST. Flag on procurement can force IGST.
        $useIgst = ($procurement->tax_type ?? 'cgst_sgst') === 'igst';
        $cgst = $useIgst ? 0 : $taxable * $cgstRate;
        $sgst = $useIgst ? 0 : $taxable * $sgstRate;
        $igst = $useIgst ? $taxable * $igstRate : 0;
        $grandTotal = $taxable + $cgst + $sgst + $igst;
        $inWords = $amountInWords($grandTotal);

        // --- Bill To (our company / buyer) ---
$billTo = [
'name' => $org->name,
'address' => trim(implode(', ', array_filter([
$clean($org->address_line1 ?? null),
$clean($org->address_line2 ?? null),
]))),
'city' => trim(implode(', ', array_filter([
$clean($org->city ?? null),
$clean($org->state ?? null),
$clean($org->pincode ?? null) ? 'Pin - ' . $org->pincode : null,
]))),
'gstin' => $clean($org->gstin ?? null),
'state_name' => $clean($org->state ?? null),
'state_code' => $clean($org->state_code ?? null),
];

// --- Ship To (consignee / delivery location) ---
$consignee = $salesOrder?->customer;
$shipAddr = $clean($procurement->shipping_address ?? null);
$shipTo = [
'name' => $consignee?->name ?? $org->name,
'address' => $shipAddr ?? trim(implode(', ', array_filter([
$clean($org->address_line1 ?? null),
$clean($org->city ?? null),
]))),
'kind_attn' => $clean($procurement->attention ?? null) ?? $clean($consignee?->contact_person ?? null),
'mobile' => $clean($consignee?->phone ?? null),
'email' => $clean($consignee?->email ?? null),
];

// --- Vendor / supplier detail resolver (uses every field the Vendor model has) ---
        $supplierDetails = [
            'Contact Person' => $clean($vendor?->particulars ?? null),
            'Mobile'         => $clean($vendor?->phone ?? null),
            'Email'          => $clean($vendor?->email ?? null),
            'GST No'         => $clean($vendor?->gst_no ?? null),
        ];

        // --- Sub-vendor specific fields (optional section) ---
        $subVendor = array_filter([
            'Sub Vendor Process'       => $clean($procurement->sub_vendor_process ?? null),
            'Operation Type'           => $clean($procurement->operation_type ?? null),
            'Raw Material Supplied By' => $clean($procurement->raw_material_supplied_by ?? null),
            'Machining Required'       => $clean($procurement->machining_required ?? null),
            'Heat Treatment Required'  => $clean($procurement->heat_treatment_required ?? null),
            'Inspection Required'      => $clean($procurement->inspection_required ?? null),
            'Packing Instructions'     => $clean($procurement->packing_instructions ?? null),
        ]);

        // Job number / marking / delivery
        $jobNo = $clean($procurement->job_no ?? null) ?? $clean($salesOrder?->order_number ?? null);
        $marking = $clean($procurement->marking_instruction ?? null) ?? 'NAME, SIZE, CLASS (PN30)';
$inspectionInstructions = $clean($procurement->inspection_instructions ?? null)
?? 'All materials must be inspected as per approved drawing and quality standards before dispatch. Material test certificate to be provided along with the supply.';
        $paymentTerms = $clean($procurement->payment_terms ?? null) ?? 'As usual with you.';

        // --- Item-level sub-vendor process fields (from PO item meta if present) ---
        $itemSvFields = [
        'sub_vendor_process' => 'Sub Vendor Process',
        'operation_type' => 'Operation Type',
        'raw_material_supplied_by' => 'Raw Material Supplied By',
        'machining_required' => 'Machining Required',
        'heat_treatment_required' => 'Heat Treatment Required',
        'inspection_required' => 'Inspection Required',
        'packing_instructions' => 'Packing Instructions',
        ];
        $getItemSvData = function ($item) use ($itemSvFields, $clean): array {
        $meta = is_array($item->item_meta ?? null) ? $item->item_meta : (json_decode($item->item_meta ?? '[]', true) ?: []);
        $out = [];
        foreach ($itemSvFields as $f => $label) {
        $v = $clean($meta[$f] ?? null);
        if ($v !== null) $out[$label] = $v;
        }
        return $out;
        };

        // --- Bill To / Ship To (org = biller, consignee / PO shipping address = shipper) ---
        $consignee = $salesOrder?->customer;
        $orgAddr = trim(implode(', ', array_filter([$clean($org->address_line1 ?? null), $clean($org->address_line2 ?? null)])));

        $billTo = [
            'name'       => $org->name ?? '-',
            'address'    => $orgAddr,
            'city'       => trim(($org->city ?? '').' '.($org->state ?? '').' '.($org->pincode ?? '')),
            'gstin'      => $clean($org->gstin ?? null),
            'state_name' => $clean($org->state ?? null),
            'state_code' => $clean($org->state_code ?? null) ?? (strlen($org->gstin ?? '') >= 2 ? substr($org->gstin, 0, 2) : null),
        ];

        $shipAddr = $clean($procurement->shipping_address ?? null)
            ?? trim(($consignee?->address ?? '').' '.($consignee?->city ?? ''))
            ?? $orgAddr;
        $shipTo = [
            'name'      => $consignee?->name ?? $org->name,
            'address'   => $shipAddr ?: '-',
            'kind_attn' => $clean($procurement->consignee_attn ?? null),
            'mobile'    => $clean($consignee?->phone ?? null) ?? $clean($org->contact_number ?? null),
            'email'     => $clean($consignee?->email ?? null),
        ];
        $deliveryDays = $clean($procurement->delivery_days ?? null)
        ?? ($procurement->expected_delivery_date
        ? (string) (int) now()->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($procurement->expected_delivery_date)->startOfDay(), false)
        : '30');
        @endphp
        <!DOCTYPE html>
        <html>

        <head>
            <meta charset="utf-8">
            <title>Sub Vendor Purchase Order {{ $procurement->po_number }}</title>
            <style>
                /* ---------- Page setup (DOMPDF) - tighter margins = more usable area ---------- */
                @page {
                    margin: 4mm 4mm;
                }

                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                html,
                body {
                    width: 100%;
                    font-family: 'Times New Roman', 'DejaVu Serif', serif;
                    font-size: 13px;
                    color: #000;
                    line-height: 1.25;
                }

                .page {
                    width: 100%;
                }

                table {
                    border-collapse: collapse;
                    width: 100%;
                    page-break-inside: avoid;
                }

                /* Long item lists must be allowed to break across pages (multi-page POs) */
                .mat-table {
                    page-break-inside: auto;
                }

                .mat-table tr {
                    page-break-inside: avoid;
                }

                td,
                th {
                    vertical-align: middle;
                    word-wrap: break-word;
                }

                /* ---------- Company header (consistent with regular PO) ---------- */
                .hdr-table td { border: 1px solid #000; padding: 4px 6px; }

                .hdr-logo  { width: 20%; text-align: center; }
                .hdr-logo img { max-width: 110px; max-height: 55px; }

                .hdr-company { width: 55%; text-align: center; }
                .hdr-company .cname { font-size: 18px; font-weight: bold; letter-spacing: .5px; }
                .hdr-company .iso { font-size: 15.5px; font-weight: bold; }
                .hdr-company .meta { font-size: 15px; }
                .hdr-company .tagline { font-size: 15px; font-weight: bold; margin-top: 1px; letter-spacing: .3px; }

                .hdr-cert { width: 25%; text-align: center; font-size: 15px; font-weight: bold; }

                /* ---------- Title ---------- */
                .po-title {
                    text-align: center;
                    font-weight: bold;
                    font-size: 21px;
                    text-decoration: underline;
                    border: 1px solid #000;
                    padding: 6px;
                    letter-spacing: 1px;
                }

                /* ---------- PO info row ---------- */
                .info-table td {
                    border: 1px solid #000;
                    padding: 4px 8px;
                    vertical-align: top;
                    font-size: 13.5px;
                }

                .info-table .lbl {
                    font-weight: bold;
                }

                .po-no-red {
                    color: #d40000;
                    font-weight: bold;
                }

                .note-yellow {
                    background: yellow;
                    font-weight: bold;
                    text-align: center;
                    font-size: 13px;
                }

                .gst-addr td {
                    border: 1px solid #000;
                    padding: 4px 8px;
                    vertical-align: top;
                    font-size: 15.5px;
                }

                .addr-title {
                    font-weight: bold;
                    font-size: 13px;
                    text-decoration: underline;
                    margin-bottom: 2px;
                }

                .addr-line {
                    white-space: pre-line;
                }

                /* ---------- Materials table ---------- */
                .mat-table td,
                .mat-table th {
                    border: 1px solid #000;
                    padding: 3px 5px;
                    font-size: 12.5px;
                }

                .mat-table th {
                    background: #d9d9d9;
                    text-align: center;
                    font-weight: bold;
                    font-size: 12.5px;
                }

                .mat-table td {
                    vertical-align: top;
                }

                .mat-table .c {
                    text-align: center;
                }

                .mat-table .r {
                    text-align: right;
                }

                .mat-table .num {
                    width: 28px;
                }

                .mat-table .desc {
                    width: 40%;
                }

                .mat-table .code {
                    width: 11%;
                    text-align: center;
                }

                .mat-table .size {
                    width: 9%;
                    text-align: center;
                }

                .mat-table .qty {
                    width: 7%;
                    text-align: right;
                }

                .mat-table .rate {
                    width: 11%;
                    text-align: right;
                }

                .mat-table .amt {
                    width: 13%;
                    text-align: right;
                }

                .item-name {
                    font-weight: bold;
                    font-size: 13px;
                }

                .item-spec {
                    font-size: 11.5px;
                    margin-top: 1px;
                }

                .item-spec .sk {
                    font-weight: bold;
                }

                .total-row td {
                    font-weight: bold;
                    background: #f2f2f2;
                }

                /* ---------- Words ---------- */
                .words-table td {
                    border: 1px solid #000;
                    padding: 4px 8px;
                    font-size: 13px;
                    vertical-align: top;
                }

                .words-table .lbl {
                    font-weight: bold;
                }

                /* ---------- Terms ---------- */
                .terms-head {
                    background: yellow;
                    font-weight: bold;
                    text-align: center;
                    border: 1px solid #000;
                    border-bottom: none;
                    padding: 2px;
                    font-size: 13px;
                    letter-spacing: 1px;
                }

                .gst-addr .lbl {
                    font-weight: bold;
                }

                .terms-table td {
                    border: 1px solid #000;
                    padding: 3px 8px;
                    font-size: 12.5px;
                    vertical-align: top;
                }

                .terms-table .no {
                    width: 4%;
                    text-align: center;
                    font-weight: bold;
                }

                .terms-table .lbl {
                    width: 18%;
                    font-weight: bold;
                }

                /* ---------- Marking ---------- */
                .marking-box {
                    background: yellow;
                    color: red;
                    font-weight: bold;
                    text-align: center;
                    font-size: 15px;
                    border: 1px solid #000;
                    padding: 4px;
                    letter-spacing: 1px;
                    line-height: 1.35;
                }

                /* ---------- Sub vendor fields ---------- */
                .sv-table td {
                    border: 1px solid #000;
                    padding: 3px 8px;
                    font-size: 12.5px;
                }

                .sv-table .lbl {
                    width: 22%;
                    font-weight: bold;
                }

                /* ---------- Signature ---------- */
                .sig-table td {
                    border: 1px solid #000;
                    padding: 4px 8px;
                    font-size: 13px;
                    vertical-align: top;
                    height: 75px;
                }

                .sig-bottom {
                    font-weight: bold;
                    text-align: center;
                    margin-top: 6px;
                }

                .sig-imgs img.signature {
                    max-height: 34px;
                }

                .sig-imgs img.seal {
                    max-height: 42px;
                }

                .footer-juris {
                    text-align: center;
                    font-weight: bold;
                    text-decoration: underline;
                    font-size: 14px;
                    padding: 4px 0;
                    border: 1px solid #000;
                    border-top: none;
                }

                /* ---------- Print CSS (browser preview) ---------- */
                @media print {
                    @page {
                        size: A4 portrait;
                        margin: 4mm 4mm;
                    }

                    body {
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }

                    .no-print {
                        display: none !important;
                    }
                }

                .no-print {
                    background: #eef2ff;
                    border: 1px solid #93a3ff;
                    padding: 10px 14px;
                    margin: 0 auto 14px;
                    width: 210mm;
                    font-family: Arial, sans-serif;
                    font-size: 16px;
                }

                .no-print a {
                    margin-right: 10px;
                    color: #1d3fb8;
                    font-weight: bold;
                }
            </style>
        </head>

        <body>

            <div class="page">
                {{-- ==================== TITLE ==================== --}}
                <div class="po-title">VENDOR PURCHASE ORDER</div>

                {{-- ==================== COMPANY HEADER (consistent with regular PO) ==================== --}}
                <table class="hdr-table">
                    <tr>
                        <td class="hdr-logo">
                            @if($logoPath)
                            <img src="{{ $logoPath }}" alt="Logo">
                            @else
                            <span style="font-size: 13px;">[ LOGO ]</span>
                            @endif
                        </td>
                        <td class="hdr-company">
                            <div class="cname">{{ strtoupper($org->name) }}</div>
                            <div class="iso">An ISO 9001:2015 Company</div>
                            <div class="meta">CIN : {{ $org->cin_number ?? 'XXXXXXXXX' }} &nbsp;|&nbsp; GSTIN : {{ $org->gstin ?? 'XXXXXXXXX' }}</div>
                            <div class="tagline">VALVES | AUTOMATION | SLUICE GATES | FLANGES &amp; FITTINGS</div>
                        </td>
                        <td class="hdr-cert">
                            @if($clean($org->address_line1 ?? null))<div>{{ $clean($org->address_line1) }}</div>@endif
                            @if($clean($org->city ?? null))<div>{{ $clean($org->city) }}</div>@endif
                            @if($clean($org->contact_number ?? null))<div>Ph : {{ $org->contact_number }}</div>@endif
                            @if($clean($org->email ?? null))<div>{{ $org->email }}</div>@endif
                        </td>
                    </tr>
                </table>
                <table class="info-table">
                    <tr>
                        <td style="width:50%;">
                            <span class="lbl">P.O. No.</span> : <span class="po-no-red">{{ $procurement->po_number }}</span>
                        </td>
                        <td style="width:50%;">
                            <span class="lbl">P.O. Date</span> : <span class="po-no-red">{{ $fmtDate($procurement->order_date) }}</span>
                        </td>
                    </tr>
                </table>
                <table class="info-table">
                    <tr>
                        <td style="width:50%;">
                            <span class="lbl">Job No :</span> {{ $jobNo ?? '-' }}
                        </td>
                        <td style="width:50%;" class="note-yellow">
                            Kindly mention our PO Number in your invoice.
                        </td>
                    </tr>
                </table>

                {{-- ==================== BILL TO / SHIP TO ==================== --}}
                <table class="gst-addr">
                    <tr>
                        <td style="width:50%;">
                            <div class="addr-title">Bill To :</div>
                            <div><strong>{{ $billTo['name'] }}</strong></div>
                            @if($billTo['address'])<div class="addr-line">{{ $billTo['address'] }}</div>@endif
                            @if($billTo['city'])<div>{{ $billTo['city'] }}</div>@endif
                            @if($clean($org->contact_number ?? null))<div>Ph : {{ $org->contact_number }}</div>@endif
                        </td>
                        <td style="width:50%;">
                            <div class="addr-title">Ship To :</div>
                            <div><strong>{{ $shipTo['name'] }}</strong></div>
                            <div class="addr-line">{{ $shipTo['address'] }}</div>
                            @if($shipTo['kind_attn'])<div>Kind Attn : {{ $shipTo['kind_attn'] }}</div>@endif
                            @if($shipTo['mobile'])<div>Mobile : {{ $shipTo['mobile'] }}</div>@endif
                            @if($shipTo['email'])<div>Email : {{ $shipTo['email'] }}</div>@endif
                        </td>
                    </tr>
                </table>

                {{-- ==================== GST DEDICATED ROW ==================== --}}
                <table class="gst-addr">
                    <tr>
                        <td style="width:50%;">
                            @if($billTo['gstin'])
                            <span class="lbl">GST No :</span> {{ $billTo['gstin'] }}
                            @if($billTo['state_name']) &nbsp;|&nbsp; State : {{ $billTo['state_name'] }}@endif
                            @if($billTo['state_code']) &nbsp;|&nbsp; Code : {{ $billTo['state_code'] }}@endif
                            @endif
                        </td>
                        <td style="width:50%;">
                            @if($clean($vendor?->gst_no ?? null))
                            <span class="lbl">Supplier GST No :</span> {{ $vendor->gst_no }}
                            @else
                            <span class="lbl">Supplier GST No :</span> N/A (Unregistered)
                            @endif
                        </td>
                    </tr>
                </table>

                {{-- ==================== SUPPLIER / VENDOR DETAILS ==================== --}}
                <table class="gst-addr">
                    <tr>
                        <td style="width:50%;">
                            <div class="addr-title">Supplier Details</div>
                            <div><strong>M/s. {{ $vendor?->name ?? '-' }}</strong></div>
                            @if($clean($vendor?->address ?? null))<div class="addr-line">{{ $vendor->address }}</div>@endif
                            @foreach($supplierDetails as $sLbl => $sVal)
                            @if($sVal)<div>{{ $sLbl }} : {{ $sVal }}</div>@endif
                            @endforeach
                        </td>
                        <td style="width:50%;">
                            <div class="addr-title">Vendor Details</div>
                            @if($clean($vendor?->particulars ?? null))<div>Kind Attn : {{ $vendor->particulars }}</div>@endif
                            @if($clean($vendor?->phone ?? null))<div>Mobile : {{ $vendor->phone }}</div>@endif
                            @if($clean($vendor?->email ?? null))<div>Email : {{ $vendor->email }}</div>@endif
                            <div>Vendor Type : {{ $vendor?->vendor_type ?? '-' }}</div>
                        </td>
                    </tr>
                </table>

                {{-- ==================== PLEASE SUPPLY THE FOLLOWING MATERIALS ==================== --}}
                <div class="terms-head" style="border-bottom:1px solid #000; background:#d9d9d9;">PLEASE SUPPLY THE FOLLOWING MATERIALS</div>

                {{-- ==================== MATERIAL TABLE ==================== --}}
                <table class="mat-table">
                    <thead>
                        <tr>
                            <th class="num">SL No</th>
                            <th class="desc">Description</th>
                            <th class="code">Item Code</th>
                            <th class="size">Size</th>
                            <th class="qty">Qty</th>
                            <th class="rate">Unit Rate (Rs.)</th>
                            <th class="amt">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        @php $product = $item->product; $specs = $getSpecs($item); $amount = (float) $item->unit_price * $item->quantity; @endphp
                        <tr>
                            <td class="c">{{ $loop->iteration }}</td>
                            <td>
                                <div class="item-name">{{ $product?->name ?? $product?->sku ?? 'N/A' }}</div>
                                <div class="item-spec">
                                    @foreach(['Drawing No', 'Revision', 'Part No', 'Material', 'Remarks'] as $key)
                                    @if(isset($specs[$key]))
                                    <span class="sk">{{ $key }} :</span> {{ $specs[$key] }}<br>
                                    @endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="c">{{ $product?->sku ?? $product?->item_code ?? '-' }}</td>
                            <td class="c">{{ $specs['Size'] ?? ($product?->valve_size ?? '-') }}</td>
                            <td class="r">{{ $fmtQty($item->quantity) }}</td>
                            <td class="r">{{ $fmtAmt($item->unit_price) }}</td>
                            <td class="r">{{ $fmtAmt($amount) }}</td>
                        </tr>
                        @endforeach

                        <tr class="total-row">
                            <td colspan="6" style="text-align:right;">Taxable Amount</td>
                            <td class="r">{{ $fmtAmt($taxable) }}</td>
                        </tr>
                        @unless($useIgst)
                        <tr class="total-row">
                            <td colspan="6" style="text-align:right;">CGST {{ number_format($cgstRate * 100, 0) }}%</td>
                            <td class="r">{{ $fmtAmt($cgst) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="6" style="text-align:right;">SGST {{ number_format($sgstRate * 100, 0) }}%</td>
                            <td class="r">{{ $fmtAmt($sgst) }}</td>
                        </tr>
                        @endunless
                        @if($useIgst)
                        <tr class="total-row">
                            <td colspan="6" style="text-align:right;">IGST {{ number_format($igstRate * 100, 0) }}%</td>
                            <td class="r">{{ $fmtAmt($igst) }}</td>
                        </tr>
                        @endif
                        <tr class="total-row">
                            <td colspan="6" style="text-align:right;">Grand Total</td>
                            <td class="r">{{ $fmtAmt($grandTotal) }}</td>
                        </tr>
                    </tbody>
                </table>

                {{-- ==================== TOTAL AMOUNT IN WORDS ==================== --}}
                <table class="words-table">
                    <tr>
                        <td class="lbl" style="width:20%;">Total Amount In Words :</td>
                        <td>{{ $inWords }}</td>
                    </tr>
                </table>

                {{-- ==================== SUB VENDOR FIELDS (optional) ==================== --}}
                @if(count($subVendor))
                <table class="sv-table" style="margin-top:2px;">
                    <tr>
                        <td class="lbl">Sub Vendor Process</td>
                        <td>{{ $subVendor['Sub Vendor Process'] }}</td>
                        <td class="lbl">Operation Type</td>
                        <td>{{ $subVendor['Operation Type'] }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Raw Material Supplied By</td>
                        <td>{{ $subVendor['Raw Material Supplied By'] ?? '-' }}</td>
                        <td class="lbl">Machining Required</td>
                        <td>{{ $subVendor['Machining Required'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Heat Treatment Required</td>
                        <td>{{ $subVendor['Heat Treatment Required'] ?? '-' }}</td>
                        <td class="lbl">Inspection Required</td>
                        <td>{{ $subVendor['Inspection Required'] ?? '-' }}</td>
                    </tr>
                    @if(isset($subVendor['Packing Instructions']))
                    <tr>
                        <td class="lbl">Packing Instructions</td>
                        <td colspan="3">{{ $subVendor['Packing Instructions'] }}</td>
                    </tr>
                    @endif
                </table>
                @endif

                {{-- ==================== TERMS & CONDITIONS ==================== --}}
                <div class="terms-head" style="margin-top:2px;">TERMS &amp; CONDITIONS</div>
                <table class="terms-table">
                    <tr>
                        <td class="no">1.</td>
                        <td class="lbl">DELIVERY TIME</td>
                        <td>Within {{ $deliveryDays }} Days.</td>
                    </tr>
                    <tr>
                        <td class="no">2.</td>
                        <td class="lbl">Payment Terms</td>
                        <td>{{ $paymentTerms }}</td>
                    </tr>
                    <tr>
                        <td class="no">3.</td>
                        <td class="lbl">Tax</td>
                        <td>GST extra as applicable @ 18% (CGST 9% + SGST 9%).</td>
                    </tr>
                    <tr>
                        <td class="no">4.</td>
                        <td class="lbl">Invoicing</td>
                        <td>Kindly mention our PO Number in your invoice.</td>
                    </tr>
                    <tr>
                        <td class="no">5.</td>
                        <td class="lbl">Transport Charges</td>
                        <td>Including up to our factory.</td>
                    </tr>
                    <tr>
                        <td class="no">6.</td>
                        <td class="lbl">DELIVERY TIME</td>
                        <td>@if($procurement->expected_delivery_date)Expected Delivery : {{ $fmtDate($procurement->expected_delivery_date) }} (Within {{ $deliveryDays }} Days).@elseWithin {{ $deliveryDays }} Days.@endif</td>
                    </tr>
                    <tr>
                        <td class="no">7.</td>
                        <td class="lbl">Marking Instructions</td>
                        <td>{{ $marking }}</td>
                    </tr>
                </table>

                {{-- ==================== MARKING INSTRUCTION (single combined section) ==================== --}}
                <table style="margin-top:2px; page-break-inside: avoid;">
                    <tr>
                        <td style="width:55%; vertical-align:middle; border:1px solid #000; padding:2px 5px;">
                            <div style="font-weight:bold; font-size:13px;">Marking Instruction</div>
                            <div style="font-size:13px;">{{ $inspectionInstructions }}</div>
                        </td>
                        <td style="width:45%;">
                            <div class="marking-box">{{ $marking }}</div>
                        </td>
                    </tr>
                </table>

                {{-- ==================== SIGNATURE SECTION ==================== --}}
                <table class="sig-table" style="margin-top:2px;">
                    <tr>
                        <td style="width:50%;">
                            <div style="font-weight:bold; text-align:center;">Customer Sign &amp; Seal<br>(Order Acceptance)</div>
                            <div class="sig-bottom">Authorized Signatory</div>
                        </td>
                        <td style="width:50%;">
                            <div style="text-align:center;">For <strong>{{ $org->name }}</strong></div>
                            <div class="sig-imgs" style="text-align:center; margin-top:4px;">
                                @if($sealPath)
                                <img class="seal" src="{{ $sealPath }}" alt="Seal">
                                @endif
                                @if($signaturePath)
                                <img class="signature" src="{{ $signaturePath }}" alt="Signature">
                                @endif
                            </div>
                            <div class="sig-bottom">Authorized Signatory</div>
                        </td>
                    </tr>
                </table>

                {{-- ==================== FOOTER ==================== --}}
                <div class="footer-juris">SUBJECT TO HOWRAH JURISDICTION</div>

            </div>

            <div class="no-print">
                Preview
                <a href="#" onclick="window.print(); return false;">Print / Save as PDF</a>
            </div>
        </body>

        </html>