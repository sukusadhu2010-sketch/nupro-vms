<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Seeds the vendors table from "Contact_Details_SC__FOUNDRY.xlsx".
 *
 * Expected sheet layout (0-indexed columns after toArray()):
 *   Column A (0) => Particulars    -> particulars (ledger/company name)
 *   Column B (1) => Contact Name   -> name
 *   Column C (2) => Mobile Nos.    -> phone
 *   Column D (3) => ADD            -> address
 *   Column E (4) => GST NO         -> gst_no
 *   Column F (5) => E-Mail ID      -> email
 *
 * The sheet is titled "For All Ledgers Under Group: Sundry Creditors
 * FOUNDRY", so every row seeded here gets vendor_type = FOUNDRY. If you
 * later add a similar "SUB VENDOR" sheet/file, reuse this seeder with
 * $vendorType changed to Vendor::VENDOR_TYPES[1] (or duplicate the class).
 *
 * Rows 1-9 in the source file are letterhead/title/header rows, so actual
 * ledger data starts at row 10 (index 9). Adjust $startRow below if your
 * file layout ever changes.
 *
 * Requires: composer require phpoffice/phpspreadsheet
 */
class VendorSeeder extends Seeder
{
    /**
     * Path to the Excel file, relative to storage/app.
     * Place Contact_Details_SC__FOUNDRY.xlsx at storage/app/seeders/ before
     * running, or update this path to wherever you keep it.
     */
    protected string $filePath = 'seeders/Contact_Details_SC__SUB.xlsx';

    /**
     * Row index (0-based) where actual ledger data begins.
     */
    protected int $startRow = 9;

    /**
     * Vendor type applied to every row in this file.
     */
    protected string $vendorType = 'SUB VENDOR';

    public function run(): void
    {
        $fullPath = storage_path('app/' . $this->filePath);

        if (! file_exists($fullPath)) {
            $this->command->error("File not found: {$fullPath}");
            $this->command->line('Place the Excel file there, or update $filePath in VendorSeeder.');
            return;
        }

        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        $now = now();
        $vendorsToInsert = [];
        $skippedRows = 0;

        foreach (array_slice($rows, $this->startRow) as $row) {
            $particulars = trim((string) ($row[0] ?? ''));
            $name = trim((string) ($row[1] ?? ''));
            $phone = $this->normalizeMultiline($row[2] ?? '');
            $address = $this->normalizeMultiline($row[3] ?? '');
            $gstNo = trim((string) ($row[4] ?? ''));
            $email = $this->normalizeMultiline($row[5] ?? '');

            // Skip blank rows (no particulars/name = not a real ledger entry)
            if ($particulars === '' && $name === '') {
                $skippedRows++;
                continue;
            }

            $vendorsToInsert[] = [
                'name' => $name !== '' ? $name : $particulars,
                'email' => $email !== '' ? $email : null,
                'phone' => $phone !== '' ? $phone : null,
                'particulars' => $particulars !== '' ? $particulars : null,
                'vendor_type' => $this->vendorType,
                'address' => $address !== '' ? $address : null,
                'status' => 'active',
                'gst_no' => $gstNo !== '' ? $gstNo : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Chunked upsert on 'gst_no' keeps this idempotent if run more than
        // once. If your gst_no column isn't unique/indexed, add a unique
        // index, or switch the uniqueBy key below to something else (e.g.
        // 'particulars'), otherwise the upsert will just behave like a plain
        // insert.
        collect($vendorsToInsert)->chunk(500)->each(function ($chunk) {
            Vendor::upsert(
                $chunk->toArray(),
                ['gst_no'],
                [
                    'name',
                    'email',
                    'phone',
                    'particulars',
                    'vendor_type',
                    'address',
                    'status',
                    'updated_at',
                ]
            );
        });

        $this->command->info(count($vendorsToInsert) . ' vendors seeded (' . $skippedRows . ' rows skipped).');
    }

    /**
     * Cells sometimes contain multiple phone numbers or emails separated by
     * newlines (e.g. "033480 15964\n033480 16753"). Collapse these into a
     * single comma-separated string instead of dropping the extra values.
     */
    protected function normalizeMultiline($value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $parts = preg_split('/[\r\n]+/', $value);
        $parts = array_filter(array_map('trim', $parts), fn ($v) => $v !== '');

        return implode(', ', $parts);
    }
}
