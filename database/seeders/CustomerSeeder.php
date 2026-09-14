<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Seeds the customers table from "Ledger_Contact_Details__SD_.xlsx".
 *
 * Expected sheet layout (0-indexed columns after toArray()):
 *   Column A (0) => Contact Name   -> name
 *   Column B (1) => Mobile Nos.    -> phone
 *   Column C (2) => ADD            -> address
 *   Column D (3) => GST NO         -> gst_no
 *   Column E (4) => E-Mail ID      -> email
 *
 * Rows 1-9 in the source file are letterhead/title/header rows, so actual
 * ledger data starts at row 10 (index 9). Adjust $startRow below if your
 * file layout ever changes.
 *
 * Requires: composer require phpoffice/phpspreadsheet
 */
class CustomerSeeder extends Seeder
{
    /**
     * Path to the Excel file, relative to storage/app.
     * Place Ledger_Contact_Details__SD_.xlsx at storage/app/seeders/ before
     * running, or update this path to wherever you keep it.
     */
    protected string $filePath = 'seeders/Ledger_Contact_Details__SD_.xlsx';

    /**
     * Row index (0-based) where actual ledger data begins.
     */
    protected int $startRow = 9;

    public function run(): void
    {
        $fullPath = storage_path('app/' . $this->filePath);

        if (! file_exists($fullPath)) {
            $this->command->error("File not found: {$fullPath}");
            $this->command->line('Place the Excel file there, or update $filePath in CustomerSeeder.');
            return;
        }

        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        $now = now();
        $customersToInsert = [];
        $skippedRows = 0;

        foreach (array_slice($rows, $this->startRow) as $row) {
            $name = trim((string) ($row[0] ?? ''));
            $phone = trim((string) ($row[1] ?? ''));
            $address = trim((string) ($row[2] ?? ''));
            $gstNo = trim((string) ($row[3] ?? ''));
            $email = trim((string) ($row[4] ?? ''));

            // Skip blank rows (no name = not a real ledger entry)
            if ($name === '') {
                $skippedRows++;
                continue;
            }

            $customersToInsert[] = [
               'name' => $name,
                'email' => $email !== '' ? $email : null,
                'phone' => $phone !== '' ? $phone : null,
                'gst_no' => $gstNo !== '' ? $gstNo : null,
                'address' => $address !== '' ? $address : null,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Chunked upsert on 'gst_no' keeps this idempotent if run more than
        // once. If your gst_no column isn't unique/indexed, add a unique
        // index, or switch the uniqueBy key below to something else (e.g.
        // 'name'), otherwise the upsert will just behave like a plain insert.
        collect($customersToInsert)->chunk(500)->each(function ($chunk) {
            Customer::upsert(
                $chunk->toArray(),
                ['gst_no'],
                ['name', 'email', 'phone', 'address', 'status', 'updated_at']
            );
        });

        $this->command->info(count($customersToInsert) . ' customers seeded (' . $skippedRows . ' rows skipped).');
    }
}
