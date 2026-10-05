<?php

namespace App\Services;

use App\Models\SalesOrder;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

class SalesOrderExportService
{
    public const COLUMNS = [
        'Our Job No',
        'PO No',
        'PO Date',
        'Customer Name',
        'Project',
        'Item Description',
        'Qty',
        'PO Value',
        'Remarks',
    ];

    /**
     * Flatten filtered sales orders into export rows (one row per order-item).
     */
    public function buildRows(Collection $salesOrders): array
    {
        $rows = [];

        foreach ($salesOrders as $order) {
            $items = $order->items->isNotEmpty()
                ? $order->items
                : collect([(object) ['product' => null, 'quantity' => null]]);

            foreach ($items as $item) {
                $rows[] = [
                    $order->job_number ?? '—',
                    $order->customer_po_number ?? '—',
                    $order->customer_po_date?->format('d-m-Y') ?? '—',
                    $order->customer->name ?? 'N/A',
                    $item->product->productCategory->name ?? '',
                    $item->product->name ?? 'N/A',
                    $item->quantity ?? '',
                    (float) $order->total_amount,
                    $item->product->description ?? '',
                ];
            }
        }

        return $rows;
    }

    /**
     * Generate the styled Excel workbook.
     */
    public function exportExcel(Collection $salesOrders, array $filters, int $totalRecords): Spreadsheet
    {
        $rows = $this->buildRows($salesOrders);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Job & PO Report');

        // ---- Report title block -------------------------------------------------
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'Job & Purchase Order Report');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'Generated on: ' . now()->format('d-m-Y H:i:s'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:I3');
        $sheet->setCellValue('A3', 'Total Records: ' . $totalRecords . ($filters ? '  |  Filters: ' . $this->describeFilters($filters) : '  |  Filters: None'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ---- Header row ---------------------------------------------------------
        $headerRow = 5;
        foreach (self::COLUMNS as $colIndex => $heading) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $coordinate = $columnLetter . $headerRow;
            $sheet->setCellValue($coordinate, $heading);
            $sheet->getStyle($coordinate)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '343A40']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }

        // ---- Data rows ----------------------------------------------------------
        foreach ($rows as $i => $row) {
            $rowIndex = $headerRow + 1 + $i;
            foreach ($row as $colIndex => $value) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue($columnLetter . $rowIndex, $value);
            }

            // PO Date text column already formatted as DD-MM-YYYY.
            // PO Value (col 8) formatted as currency.
            $sheet->getStyle('H' . $rowIndex)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A' . $rowIndex . ':I' . $rowIndex)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // ---- Auto-adjust column widths -----------------------------------------
        foreach (range('A', 'I') as $column) {
            $width = 12;
            $headerLen = mb_strlen(self::COLUMNS[ord($column) - ord('A')]);
            $width = max($width, $headerLen + 4);

            foreach ($rows as $row) {
                $len = mb_strlen((string) ($row[ord($column) - ord('A')] ?? ''));
                $width = max($width, min($len + 2, 60));
            }

            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->freezePane('A' . ($headerRow + 1));

        return $spreadsheet;
    }

    /**
     * Stream the spreadsheet to the browser.
     */
    public function downloadExcel(Spreadsheet $spreadsheet): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'Job-PO-Report-' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Generate the professional landscape PDF report.
     */
    public function exportPdf(Collection $salesOrders, array $filters, int $totalRecords): \Illuminate\Http\Response
    {
        $rows = $this->buildRows($salesOrders);
        $org = \App\Models\OrganizationSetting::current();
        $logoPath = public_path('storage/' . $org->logo_path);
        $logoBase64 = null;
        if ($org->logo_path && file_exists($logoPath)) {
            $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $mime = $ext === 'jpg' ? 'jpeg' : $ext;
            $logoBase64 = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }

        $html = view('admin.sales-orders.exports.pdf', [
            'rows' => $rows,
            'columns' => self::COLUMNS,
            'filters' => $filters,
            'filterSummary' => $this->describeFilters($filters),
            'totalRecords' => $totalRecords,
            'org' => $org,
            'logoBase64' => $logoBase64,
            'generatedAt' => now()->format('d-m-Y H:i:s'),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Job-PO-Report-' . now()->format('Ymd_His') . '.pdf"',
        ]);
    }

    /**
     * Human-readable summary of applied filters (used in exports).
     */
    public function describeFilters(array $filters): string
    {
        $parts = [];

        if (! empty($filters['date_from']) || ! empty($filters['date_to'])) {
            $parts[] = 'Date: ' . ($filters['date_from'] ?? 'start') . ' to ' . ($filters['date_to'] ?? 'today')
                . ' (' . ($filters['date_field'] === 'created_at' ? 'Created Date' : 'PO Date') . ')';
        }

        if (! empty($filters['status'])) {
            $parts[] = 'Status: ' . ucfirst($filters['status']);
        }

        if (! empty($filters['customer_id'])) {
            $parts[] = 'Customer ID: ' . $filters['customer_id'];
        }

        if (! empty($filters['product_id'])) {
            $parts[] = 'Project ID: ' . $filters['product_id'];
        }

        if (! empty($filters['job_number'])) {
            $parts[] = 'Job No: ' . $filters['job_number'];
        }

        if (! empty($filters['po_no'])) {
            $parts[] = 'PO No: ' . $filters['po_no'];
        }

        if (! empty($filters['remarks'])) {
            $parts[] = 'Remarks: ' . $filters['remarks'];
        }

        return implode(' | ', $parts);
    }
}
