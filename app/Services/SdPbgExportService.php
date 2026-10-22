<?php

namespace App\Services;

use App\Models\BankGuarantee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SdPbgExportService
{
    public const COLUMNS = [
        'SL No',
        'Client',
        'BG No.',
        'BG Date',
        'BG Expiry Date',
        'Claim Date',
        'Claim Amount',
        'PO No.',
        'PO Date',
        'Invoice No.',
        'Invoice Date',
        'Status',
    ];

    /** Date columns (0-based row indexes within self::COLUMNS). */
    private const DATE_COLS = [3, 4, 5, 8, 10]; // BG Date, Expiry, Claim, PO Date, Invoice Date

    /** Claim Amount column letter (G). */
    private const AMOUNT_COL = 'G';

    /**
     * Flatten bank guarantees into export rows.
     *
     * @return array<int, array<int, mixed>>
     */
    public function buildRows(Collection $guarantees): array
    {
        $rows = [];
        $sl = 1;

        foreach ($guarantees as $bg) {
            $rows[] = [
                $sl++,
                $bg->client_name,
                $bg->bg_number,
                $bg->bg_date?->format('d-m-Y') ?? '',
                $bg->expiry_date?->format('d-m-Y') ?? '',
                $bg->claim_date?->format('d-m-Y') ?? '',
                (float) ($bg->claim_amount ?? 0),
                $bg->salesOrder?->customer_po_number ?? '',
                $bg->salesOrder?->customer_po_date?->format('d-m-Y') ?? '',
                $bg->invoice->invoice_number ?? '',
                $bg->invoice?->invoice_date?->format('d-m-Y') ?? '',
                ucfirst($bg->status),
            ];
        }

        return $rows;
    }

    /**
     * Build the styled Excel workbook.
     */
    public function exportExcel(Collection $guarantees, array $filters, int $totalRecords): Spreadsheet
    {
        $rows = $this->buildRows($guarantees);
        $columnCount = count(self::COLUMNS);
        $lastCol = Coordinate::stringFromColumnIndex($columnCount);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('SD-PBG Register');

        // ---- Title block -------------------------------------------------------
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'SD/PBG Register Report');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Generated on: ' . now()->format('d-m-Y H:i:s')
            . '  |  User: ' . (auth()->user()?->name ?? 'System'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'Total Records: ' . $totalRecords
            . '  |  Filters: ' . ($filters ? $this->describeFilters($filters) : 'None'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ---- Header row ---------------------------------------------------------
        $headerRow = 5;
        foreach (self::COLUMNS as $index => $heading) {
            $coordinate = Coordinate::stringFromColumnIndex($index + 1) . $headerRow;
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
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($colIndex + 1) . $rowIndex,
                    $value
                );
            }

            // Currency format on Claim Amount (G)
            $sheet->getStyle(self::AMOUNT_COL . $rowIndex)
                ->getNumberFormat()->setFormatCode('#,##0.00');

            // Borders for the whole row
            $sheet->getStyle('A' . $rowIndex . ':' . $lastCol . $rowIndex)
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Alternate row shading
            if ($i % 2 === 1) {
                $sheet->getStyle('A' . $rowIndex . ':' . $lastCol . $rowIndex)
                    ->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']],
                    ]);
            }
        }

        // ---- Auto-fit column widths ---------------------------------------------
        foreach (range(1, $columnCount) as $index) {
            $columnLetter = Coordinate::stringFromColumnIndex($index);
            $width = mb_strlen(self::COLUMNS[$index - 1]) + 4;

            foreach ($rows as $row) {
                $len = mb_strlen((string) ($row[$index - 1] ?? ''));
                $width = max($width, min($len + 2, 60));
            }

            $sheet->getColumnDimension($columnLetter)->setWidth($width);
        }

        // Freeze header + enable Excel filters on the header row
        $sheet->freezePane('A' . ($headerRow + 1));
        $sheet->setAutoFilter('A' . $headerRow . ':' . $lastCol . ($headerRow + max(count($rows), 0)));

        return $spreadsheet;
    }

    /**
     * Stream the workbook as a download: SD_PBG_Register_YYYYMMDD_HHMMSS.xlsx
     */
    public function downloadExcel(Spreadsheet $spreadsheet): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'SD_PBG_Register_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Human-readable summary of applied filters.
     */
    public function describeFilters(array $filters): string
    {
        $parts = [];

        if (! empty($filters['client'])) {
            $parts[] = 'Client: ' . $filters['client'];
        }
        if (! empty($filters['bg_number'])) {
            $parts[] = 'BG No: ' . $filters['bg_number'];
        }
        if (! empty($filters['po_no'])) {
            $parts[] = 'PO No: ' . $filters['po_no'];
        }
        if (! empty($filters['invoice_no'])) {
            $parts[] = 'Invoice No: ' . $filters['invoice_no'];
        }
        if (! empty($filters['status'])) {
            $parts[] = 'Status: ' . ucfirst($filters['status']);
        }
        if (! empty($filters['bg_date_from']) || ! empty($filters['bg_date_to'])) {
            $parts[] = 'BG Date: ' . ($filters['bg_date_from'] ?? 'start') . ' to ' . ($filters['bg_date_to'] ?? 'today');
        }
        if (! empty($filters['expiry_from']) || ! empty($filters['expiry_to'])) {
            $parts[] = 'Expiry: ' . ($filters['expiry_from'] ?? 'start') . ' to ' . ($filters['expiry_to'] ?? 'today');
        }
        if (! empty($filters['claim_from']) || ! empty($filters['claim_to'])) {
            $parts[] = 'Claim Date: ' . ($filters['claim_from'] ?? 'start') . ' to ' . ($filters['claim_to'] ?? 'today');
        }

        return implode(' | ', $parts);
    }
}
