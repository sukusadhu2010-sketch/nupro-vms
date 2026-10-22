﻿{{-- ============================================================
     Foundry Purchase Order â€” Print / PDF Template (A4 Portrait)
     DOMPDF compatible. Usage:
       return PDF::loadView('purchase-orders.print', compact('procurement'))->stream('PO.pdf');
     or render as HTML for browser print preview (is_html_preview=true).
     ============================================================ --}}

@php
$org = \App\Models\OrganizationSetting::current();
$vendor = $procurement->vendor;
$salesOrder = $procurement->salesOrder ?? $procurement->salesOrders->first();
$items = $procurement->items()->with(['product', 'salesOrderItem.product'])->get();

// --- Smart blank rows: small POs get a couple of spare rows, big POs get none ---
$itemCount = $items->count();
$blankRows = $itemCount <= 5 ? max(1, 5 - $itemCount) : 0;

// --- Absolute paths for DOMPDF (it cannot read URLs) ---
$assetPath = function (?string $storagePath): ?string {
    if (! $storagePath) return null;
    return \Storage::disk('public')->path($storagePath);
};
$logoPath =($org->logo_path) ? url('/').'/storage/'.$org->logo_path : null;
$signaturePath = ($org->signature_path) ? url('/').'/storage/'.$org->signature_path : null;
$sealPath = ($org->seal_path) ? url('/').'/storage/'.$org->seal_path : null;

// --- Formatters ---
$fmtDate = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('d-M-Y') : '-';
$fmtQty  = fn ($q) => rtrim(rtrim(number_format((float) $q, 2, '.', ''), '0'), '.') ?: '0';
$fmtAmt  = fn ($a) => number_format((float) $a, 2);

// Clean helper: trim, drop nulls / placeholders / junk single-char values
$clean = function ($v): ?string {
    if ($v === null) return null;
    $v = trim((string) $v);
    if ($v === '' || strtolower($v) === 'null' || strtolower($v) === 'na' || strtolower($v) === 'n/a' || strtolower($v) === '-') return null;
    // Discard 1-2 char placeholder junk like "bb", "x", "--"
    if (mb_strlen(preg_replace('/[^A-Za-z0-9]/', '', $v) ?? '') < 2) return null;
    return $v;
};

// Delivery period â€” never print a raw timestamp
$deliveryPeriod = $clean($procurement->delivery_time ?? null) ?? '30 Days';
$deliveryDate = $procurement->expected_delivery_date ?? $salesOrder?->expected_delivery_date;
if ($deliveryDate) {
    $d = \Illuminate\Support\Carbon::parse($deliveryDate);
    $days = (int) now()->startOfDay()->diffInDays($d->copy()->startOfDay(), false);
    $dateText = $d->format('d-M-Y') . ($days > 0 && $days <= 60 ? " ({$days} Days)" : '');
    // If delivery_time already contains a date, prefer the entered delivery time text
    $deliveryPeriod = preg_match('/\d{1,2}[-\/ ]\d{1,2}[-\/ ]\d{2,4}|\d{1,2}-[A-Za-z]{3}/', $deliveryPeriod)
        ? $deliveryPeriod
        : $dateText . (str_contains($deliveryPeriod, 'Day') ? " {$deliveryPeriod}" : '');
}

// --- Product technical info resolver (Product Master + SO item + PO notes) ---
$specFields = [
    'drawing_no' => 'Drawing No', 'revision_no' => 'Revision No', 'part_no' => 'Part No',
    'material_grade' => 'Material Grade', 'item_weight' => 'Item Weight', 'pattern_no' => 'Pattern No',
    'casting_type' => 'Casting Type', 'pressure_class' => 'Pressure Class', 'valve_size' => 'Valve Size',
    'end_connection' => 'End Connection', 'testing_standard' => 'Testing Standard',
    'inspection_requirement' => 'Inspection Requirement',
    'casting_weight' => 'Casting Weight', 'heat_treatment' => 'Heat Treatment',
    'machining_allowance' => 'Machining Allowance', 'customer_part_no' => 'Customer Part No',
    'moc' => 'MOC', 'mfg_spec' => 'MFG Spec', 'trim' => 'Trim', 'operation' => 'Operation',
    'rating' => 'Rating', 'media' => 'Media',
];
$getSpecs = function ($item) use ($specFields, $clean): array {
    $src = [];
    foreach ([$item->salesOrderItem, $item->product] as $obj) {
        if (! $obj) continue;
        foreach ($specFields as $f => $label) {
            $v = $clean($obj->{$f} ?? null);
            if ($v !== null) $src[$label] = $v;
        }
    }
    // Free-text notes from the PO line â€” shown as "Remarks"
    if ($r = $clean($item->notes)) $src['Remarks'] = $r;
    elseif ($r = $clean($item->salesOrderItem->product->description ?? $item->product->description ?? null)) $src['Description'] = $r;
    return $src;
};

$totalAmount = $items->sum(fn ($i) => (float) $i->unit_price * $i->quantity);
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $procurement->po_number }}</title>
    <style>
        /* ---------- Page setup (DOMPDF) â€” margins INSIDE the page, content never touches the edge ---------- */
        @page {
            margin: 4mm 4mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            font-family: 'Times New Roman', 'DejaVu Serif', serif;
            font-size: 12px;
            color: #000;
            line-height: 1.15;
        }

        .page {
            width: 100%;
            border: 1px solid #000;
            padding: 4px;
            
        }

        table {
            border-collapse: collapse;
            width: 100%;
            page-break-inside: avoid;
        }

        td, th {
            vertical-align: middle;
            word-wrap: break-word;
        }

        /* ---------- Header â€” fixed columns: Logo 20 / Company 55 / Contact 25 ---------- */
        .hdr-table td { border: 1px solid #000; padding: 3px 5px; }

        .hdr-logo  { width: 20%; text-align: center; }
        .hdr-logo img { max-width: 100px; max-height: 48px; }

        .hdr-company { width: 55%; text-align: center; }
        .hdr-company .cname { font-size: 17px; font-weight: bold; letter-spacing: .5px; }
        .hdr-company .iso { font-size: 12px; font-weight: bold; }
        .hdr-company .meta { font-size: 11.5px; }
        .hdr-company .tagline { font-size: 11.5px; font-weight: bold; margin-top: 1px; letter-spacing: .3px; }

        .hdr-cert { width: 25%; text-align: center; font-size: 11.5px; font-weight: bold; }

        /* ---------- Title ---------- */
        .po-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 2px;
            padding: 3px 0;
            border: 1px solid #000;
            border-top: none;
        }

        /* ---------- PO info / vendor block ---------- */
        .info-table td { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
        .info-table .lbl { font-weight: bold; display: inline-block; width: 52px; }
        .info-row { margin-bottom: 1px; }
        .info-title { font-weight: bold; font-size: 12px; margin-bottom: 1px; text-decoration: underline; }
        .addr-line { white-space: pre-line; }

        /* ---------- GST row ---------- */
        .gst-row { border: 1px solid #000; border-top: none; background: #d9d9d9; text-align: center; font-weight: bold; font-size: 13px; padding: 2px; }

        /* ---------- Materials table ---------- */
        .section-head { border: 1px solid #000; text-align: center; font-weight: bold; font-size: 13px; padding: 2px; letter-spacing: .5px; }

        .mat-table td, .mat-table th { border: 1px solid #000; padding: 2px 3px; font-size: 11.5px; }
        .mat-table th { background: #d9d9d9; text-align: center; font-weight: bold; font-size: 11.5px; }
        .mat-table td { vertical-align: top; }
        .mat-table .c { text-align: center; }
        .mat-table .r { text-align: right; }
        .mat-table .num  { width: 28px; }
        .mat-table .desc { width: 46%; }
        .mat-table .moc  { width: 12%; text-align: center; }
        .mat-table .qty  { width: 8%;  text-align: right; }
        .mat-table .wt   { width: 12%; text-align: right; }
        .mat-table .rate { width: 12%; text-align: right; }

        .item-name { font-weight: bold; font-size: 12px; }
         .item-spec { font-size: 10.5px; color: #000; margin-top: 1px; }
        .item-spec .sk { font-weight: bold; }

        .total-row td { font-weight: bold; background: #f2f2f2; }

        /* ---------- Terms / specs / foundry sections ---------- */
        .terms-table td { border: 1px solid #000; padding: 2px 5px; font-size: 11.5px; vertical-align: top; }
        .terms-table .lbl { width: 20%; font-weight: bold; }

        .box-head { border: 1px solid #000; font-weight: bold; font-size: 12px; background: #f2f2f2; padding: 2px 5px; }

        /* ---------- Marking box (single, yellow highlight) ---------- */
        .marking-box {
            background: yellow;
            font-weight: bold;
            text-align: center;
            font-size: 15px;
            border: 1px solid #000;
            padding: 4px 3px;
            letter-spacing: 1px;
            line-height: 1.35;
        }

        /* ---------- Inspection ---------- */
        .insp-table td { border: 1px solid #000; padding: 2px 5px; font-size: 11.5px; vertical-align: top; }
        .insp-table ol { margin-left: 14px; }
        .insp-table li { margin-bottom: 1px; }
        .insp-head { font-weight: bold; font-size: 12px; background: #f2f2f2; padding: 2px 5px; border: 1px solid #000; }

        /* ---------- Foundry requirements ---------- */
        .fr-table td { border: 1px solid #000; padding: 2px 5px; font-size: 11.5px; }
        .fr-table .lbl { width: 20%; font-weight: bold; }

        /* ---------- Signature + Vendor acceptance (compact) ---------- */
        .sig-table td { border: 1px solid #000; padding: 3px 5px; }
        .sig-right { width: 25%; text-align: right; vertical-align: bottom; }
        .sig-imgs img.signature { max-height: 34px; }
        .sig-imgs img.seal { max-height: 42px; }
        .sig-line { font-weight: bold; margin-top: 3px; }
        .sign-cell { font-size: 11.5px; vertical-align: top; padding: 3px 4px; }
        .accept-box { border: 1px solid #000; padding: 3px 6px; font-size: 11.5px; }
        .accept-inner td { border: none !important; padding: 1px 3px !important; font-size: 11.5px; }

        /* ---------- Print CSS (browser preview) ---------- */
        @media print {
            @page { size: A4 portrait; margin: 4mm 4mm; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
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
        .no-print a { margin-right: 10px; color: #1d3fb8; font-weight: bold; }
    </style>
</head>

<body>

  
   
  

    <div class="page">

        {{-- ==================== COMPANY HEADER ==================== --}}
        <table class="hdr-table">
            <tr>
                <td class="hdr-logo">
                    @if($logoPath && file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Logo">
                    @else
                    <span style="font-size: 15.5px;">[ LOGO ]</span>
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

        {{-- ==================== TITLE ==================== --}}
        <div class="po-title">PURCHASE ORDER</div>

        {{-- ==================== PO INFO + VENDOR ==================== --}}
        <table class="info-table">
            <tr>
                <td style="width:50%;">
                    <div class="info-row"><span class="lbl">PO No</span> : {{ $procurement->po_number }}</div>
                    <div class="info-row"><span class="lbl">Date</span> : {{ $fmtDate($procurement->order_date) }}</div>
                    <div class="info-row"><span class="lbl">Job No</span> : {{ $salesOrder?->order_number ?? ($salesOrder?->id ? 'SO-'.$salesOrder->id : '-') }}</div>
                    <div class="info-row"><span class="lbl">DELIVERY TIME</span> : {{ $deliveryPeriod }}</div>
                </td>
                <td style="width:50%;">
                    <div class="info-title">To:</div>
                    <div><strong>{{ $vendor?->name ?? '-' }}</strong></div>
                    @if($clean($vendor?->address ?? null))<div class="addr-line">{{ $vendor->address }}</div>@endif
                    @if($clean($vendor?->phone ?? null))<div>Ph : {{ $vendor->phone }}</div>@endif
                    @if($clean($vendor?->email ?? null))<div>Email : {{ $vendor->email }}</div>@endif
                    @if($clean($vendor?->gst_no ?? null))<div>GSTIN : {{ $vendor->gst_no }}</div>@endif
                </td>
            </tr>
        </table>

        {{-- ==================== BILL TO / SHIP TO ==================== --}}
        <table class="info-table" style="margin-top:2px;">
            <tr>
                <td style="width:50%;">
                    <div class="info-title">Bill To:</div>
                    <div><strong>{{ $org->name }}</strong></div>
                    @if($clean($org->address_line1 ?? null))<div>{{ $org->address_line1 }}</div>@endif
                    @if($clean($org->address_line2 ?? null))<div>{{ $org->address_line2 }}</div>@endif
                    <div>{{ trim(($org->city ?? '').' '.($org->state ?? '').' '.($org->pincode ?? '')) }}</div>
                    @if($clean($org->contact_number ?? null))<div>Ph : {{ $org->contact_number }}</div>@endif
                </td>
                <td style="width:50%;">
                    <div class="info-title">Ship To:</div>
                    @php
                        $shipAddr = $clean($procurement->shipping_address ?? null);
                        $shipLines = $shipAddr ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $shipAddr)), fn($l) => $l !== '')) : [];
                    @endphp
                    <div><strong>{{ $org->name }}</strong> (Works)</div>
                    @forelse($shipLines as $line)
                        <div>{{ $line }}</div>
                    @empty
                        @if($clean($org->address_line1 ?? null))<div>{{ $org->address_line1 }}</div>@endif
                        <div>{{ trim(($org->city ?? '').' '.($org->state ?? '').' '.($org->pincode ?? '')) }}</div>
                    @endforelse
                    @if($clean($org->contact_number ?? null))<div>Ph : {{ $org->contact_number }}</div>@endif
                </td>
            </tr>
        </table>

        {{-- ==================== GST ROW ==================== --}}
        <div class="gst-row">GST NUMBER : {{ $vendor?->gst_no ?? $org->gstin ?? '-' }}</div>

        {{-- ==================== MATERIALS ==================== --}}
        <div class="section-head">PLEASE SUPPLY THE FOLLOWING MATERIALS</div>
        <table class="mat-table">
            <thead>
                <tr>
                    <th class="num">SL No</th>
                    <th class="desc">Item Description</th>
                    <th class="moc">MOC</th>
                    <th class="qty">QTY<br><span style="font-weight:normal;">(Nos)</span></th>
                    <th class="wt">APPX WEIGHT<br><span style="font-weight:normal;">(Kgs)</span></th>
                    <th class="rate">RATE PER KG<br><span style="font-weight:normal;">(Rs)</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                @php $product = $item->product; $specs = $getSpecs($item); @endphp
                <tr>
                    <td class="c">{{ $loop->iteration }}</td>
                    <td>
                        <div class="item-name">{{ $product?->sku ?? $product?->name ?? 'N/A' }}</div>
                        <div class="item-spec">
                            @if($product?->name && $product->name !== $product->sku)
                                <span class="sk">Description :</span> {{ $product->name }}<br>
                            @endif
                            @foreach($specs as $specLabel => $specValue)
                                <span class="sk">{{ $specLabel }} :</span> {{ $specValue }}<br>
                            @endforeach
                            @if($product?->hsn_code)
                                <span class="sk">HSN :</span> {{ $product->hsn_code }}
                            @endif
                        </div>
                    </td>
                    <td class="c">{{ $specs['MOC'] ?? $specs['Material Grade'] ?? ($product?->material ?? '-') }}</td>
                    <td class="r">{{ $fmtQty($item->quantity) }}</td>
                    <td class="r">{{ $specs['Item Weight'] ?? $specs['Casting Weight'] ?? '-' }}</td>
                    <td class="r">{{ $fmtAmt($item->unit_price) }}</td>
                </tr>
                @endforeach

                {{-- spare rows only for very small POs --}}
                @for($i = 0; $i < $blankRows; $i++)
                    <tr><td>&nbsp;</td><td></td><td colspan="4"></td></tr>
                @endfor

                <tr class="total-row">
                    <td colspan="5" style="text-align:right;">Total</td>
                    <td class="r">{{ $fmtAmt($totalAmount) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- ==================== COMMERCIAL TERMS ==================== --}}
        <table class="terms-table" style="margin-top:2px;">
            <tr>
                <td class="lbl">DELIVERY TIME :</td>
                <td>{{ $deliveryPeriod }}</td>
            </tr>
            <tr>
                <td class="lbl">Payment Terms :</td>
                <td>90 Days from receipt of materials</td>
            </tr>
            <tr>
                <td class="lbl">Tax :</td>
                <td>GST 18% Extra</td>
            </tr>
            <tr>
                <td class="lbl">Invoicing :</td>
                <td>Our PO number must be mentioned on invoice and submit one copy of Purchase Order.</td>
            </tr>
            <tr>
                <td class="lbl">Transport Charges :</td>
                <td>Including up to our factory.</td>
            </tr>
        </table>

        {{-- ==================== MARKING INSTRUCTION (single combined section) ==================== --}}
        <table style="margin-top:2px;">
            <tr>
                <td style="width:55%; vertical-align:middle; border:1px solid #000; padding:2px 5px;">
                    <div class="insp-head">Marking Instruction</div>
                    <div style="font-size: 13px;">Every casting shall bear foundry heat number, item identification and casting weight as per our marking layout.</div>
                </td>
                <td style="width:45%;">
                    <div class="marking-box">
                        NUPRO<br>
                        HEAT NO<br>
                        VALVE SIZE<br>
                        CLASS
                    </div>
                </td>
            </tr>
        </table>

        {{-- ==================== FOUNDRY REQUIREMENTS ==================== --}}
        <div class="box-head" style="margin-top:2px;">FOUNDRY REQUIREMENTS</div>
        <table class="fr-table">
            <tr>
                <td class="lbl">Pattern Supplied By</td>
                <td>NUPRO (Free Issue)</td>
                <td class="lbl">Pattern Returnable</td>
                <td>Yes</td>
                <td class="lbl">MTC Required</td>
                <td>{{ ($salesOrder?->mtc ?? false) ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td class="lbl">PDI Required</td>
                <td>{{ ($salesOrder?->pdi ?? false) ? 'Yes' : 'No' }}</td>
                <td class="lbl">Stage Inspection Required</td>
                <td>Yes</td>
                <td class="lbl">Third Party Inspection</td>
                <td>As Applicable</td>
            </tr>
            <tr>
                <td class="lbl">Heat No Mandatory</td>
                <td>Yes</td>
                <td class="lbl">Casting Weight Tolerance</td>
                <td>Â± 5% or As Per Drawing</td>
                <td class="lbl">Machining Allowance</td>
                <td>As Per Drawing</td>
            </tr>
            <tr>
                <td class="lbl">Raw Casting / Finished Casting</td>
                <td>Raw Casting (Unmachined)</td>
                <td class="lbl">Testing Standard</td>
                <td>As Per IS / Industry Standard</td>
                <td class="lbl">Inspection Requirement</td>
                <td>Vendor Routine + PDI</td>
            </tr>
        </table>

        {{-- ==================== INSPECTION INSTRUCTIONS ==================== --}}
        <div class="insp-head" style="margin-top:2px;">Inspection Instructions</div>
        <table class="insp-table">
            <tr>
                <td>
                    <ol>
                        <li>As per pattern and Industry Standards.</li>
                        <li>In-process inspection to be done for dimensional and visual checking.</li>
                        <li>Any blow holes, shrinkage, casting defects or material defects shall be replaced by vendor at vendor cost.</li>
                        <li>Rejected materials shall be replaced free of cost by supplier.</li>
                    </ol>
                </td>
            </tr>
        </table>

        {{-- ==================== SIGNATURE ROW ==================== --}}
        <table class="sig-table" style="margin-top:2px;">
            <tr>
                <td class="sign-cell" style="width:25%; border:1px solid #000;">
                    <div><strong>Prepared By</strong></div>
                    <div style="margin-top:8px;">Admin</div>
                    <div style="margin-top:6px;">Date : {{ $fmtDate($procurement->created_at ?? $procurement->order_date) }}</div>
                </td>
                <td class="sign-cell" style="width:25%; border:1px solid #000;">
                    <div><strong>Checked By</strong></div>
                    <div style="margin-top:8px;">&nbsp;</div>
                    <div style="margin-top:6px;">Date : ______________</div>
                </td>
                <td class="sign-cell" style="width:25%; border:1px solid #000;">
                    <div><strong>Approved By</strong></div>
                    <div style="margin-top:8px;">Purchase Manager</div>
                    <div style="margin-top:6px;">Date : {{ $fmtDate($procurement->order_date) }}</div>
                </td>
                <td class="sig-right" style="border:1px solid #000; padding:3px 5px;">
                    <div class="sig-imgs">
                        @if($sealPath)
                        <img class="seal" src="{{ $sealPath }}" alt="Seal">
                        @endif
                        @if($signaturePath )
                        <img class="signature" src="{{ $signaturePath }}" alt="Signature">
                        @endif
                    </div>
                    <div style="margin-top:2px;">For <strong>{{ $org->name }}</strong></div>
                    <div class="sig-line">Authorized Signatory</div>
                </td>
            </tr>
        </table>

        {{-- ==================== VENDOR ACCEPTANCE (compact) ==================== --}}
        <table style="margin-top:2px;">
            <tr>
                <td class="accept-box">
                    <strong>We accept the above terms and conditions and confirm our ability to execute this order.</strong>
                    <table class="accept-inner" style="margin-top:2px;">
                        <tr>
                            <td><strong>Vendor Acceptance</strong></td>
                            <td>Name : ____________________</td>
                            <td>Signature : ____________________</td>
                            <td>Date : ______________</td>
                            <td>Seal : ______________</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

    </div>
 <div class="no-print">
        Preview
        <a href="#"  class="btn btn-primary" onclick="window.print(); return false;">Print / Save as PDF</a>
    </div>
</body>

</html>
