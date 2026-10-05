<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #212529;
        }

        .report-header {
            border-bottom: 2px solid #343a40;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .report-header table {
            width: 100%;
        }

        .report-header .logo img {
            max-height: 55px;
            max-width: 160px;
        }

        .report-header .title {
            font-size: 18px;
            font-weight: bold;
        }

        .report-header .subtitle {
            font-size: 10px;
            color: #6c757d;
        }

        .filters-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 10px;
            font-size: 9px;
        }

        .filters-box strong {
            color: #343a40;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data thead {
            display: table-header-group;
        }

        table.data th {
            background: #343a40;
            color: #ffffff;
            padding: 6px 5px;
            text-align: left;
            font-size: 9px;
            border: 1px solid #212529;
        }

        table.data td {
            padding: 5px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        table.data tbody tr:nth-child(even) td {
            background: #f8f9fa;
        }

        .num {
            text-align: right;
        }

        .desc {
            min-width: 130px;
            word-wrap: break-word;
        }

        table.data tfoot {
            display: table-footer-group;
        }

        table.data tfoot td {
            font-weight: bold;
            background: #e9ecef;
            border: 1px solid #dee2e6;
            padding: 6px 5px;
        }

        .footer-note {
            margin-top: 12px;
            font-size: 8px;
            color: #6c757d;
        }
    </style>
</head>

<body>

    <div class="report-header">
        <table>
            <tr>
                <td class="logo" width="18%">
                    @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="logo">
                    @else
                    <span style="font-size: 14px; font-weight: bold;">{{ $org->name }}</span>
                    @endif
                </td>
                <td width="52%">
                    <div class="title">Job &amp; Purchase Order Report</div>
                    <div class="subtitle">Generated on: {{ $generatedAt }}</div>
                </td>
                <td width="30%" style="text-align: right;">
                    <div style="font-size: 12px; font-weight: bold;">Total Records: {{ $totalRecords }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="filters-box">
        <strong>Applied Filters:</strong>
        {{ $filters ? $filterSummary : 'None — showing all records' }}
    </div>

    <table class="data">
        <thead>
            <tr>
                @foreach ($columns as $column)
                <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
            <tr>
                <td>{{ $row[0] }}</td>
                <td>{{ $row[1] }}</td>
                <td>{{ $row[2] }}</td>
                <td>{{ $row[3] }}</td>
                <td>{{ $row[4] }}</td>
                <td class="desc">{{ $row[5] }}</td>
                <td class="num">{{ $row[6] }}</td>
                <td class="num">{{ number_format($row[7], 2) }}</td>
                <td class="desc">{{ $row[8] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">No records found for the selected filters.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="9">Total Records: {{ $totalRecords }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-note">
        This report was generated automatically by the Job/PO Management Module. Page numbers are rendered by the PDF engine.
    </div>

    <script type="text/php">
        if (isset($pdf) && isset($page)) {
            $font = $pdf->getFont("helvetica", "normal", 8);
            $pdf->text(500, 570, "Page $page of {PAGE_NUM}", $font, 8, array(0.4, 0.4, 0.4));
        }
    </script>
</body>

</html>