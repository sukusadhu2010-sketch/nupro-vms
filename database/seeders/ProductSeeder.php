<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Seeds the products table from the "STOCK_GROUP_SUMMERY.xlsx" file.
 *
 * Expected sheet layout (0-indexed columns after toArray()):
 *   Column A (0) => Particulars / Item code  -> used as name + sku
 *   Column B (1) => HSN CODE                 -> hsn_code
 *   Column C (2) => Category / product type  -> category text + description
 *
 * Rows 1-3 are title/header rows in the source file, so actual product
 * data starts at row 4 (index 3). Adjust $startRow below if your file
 * layout ever changes.
 *
 * Requires: composer require phpoffice/phpspreadsheet
 */
class ProductSeeder extends Seeder
{
    /**
     * Path to the Excel file, relative to storage/app.
     * Place STOCK_GROUP_SUMMERY.xlsx at storage/app/seeders/ before running,
     * or update this path to wherever you keep it.
     */
    protected string $filePath = 'seeders/STOCK_GROUP_SUMMERY.xlsx';

    /**
     * Row index (0-based) where actual product data begins.
     */
    protected int $startRow = 3;

    public function run(): void
    {
        $fullPath = storage_path('app/' . $this->filePath);

        if (! file_exists($fullPath)) {
            $this->command->error("File not found: {$fullPath}");
            $this->command->line('Place the Excel file there, or update $filePath in ProductSeeder.');
            return;
        }

        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        $categoryCache = [];
        $now = now();
        $productsToInsert = [];
        $skippedRows = 0;

        foreach (array_slice($rows, $this->startRow) as $row) {
            $itemCode = trim((string) ($row[0] ?? ''));
            $hsnCode = trim((string) ($row[1] ?? ''));
            $categoryName = trim((string) ($row[2] ?? ''));

            // Skip blank rows or trailing footer/notes rows (e.g. "DESCRIPTION:-")
            if ($itemCode === '' || $categoryName === '') {
                $skippedRows++;
                continue;
            }
            if (Str::contains(Str::upper($itemCode), 'DESCRIPTION')) {
                $skippedRows++;
                continue;
            }

            $categoryId = $this->resolveCategoryId($categoryName, $categoryCache);

            $productsToInsert[] = [
                'name' => $itemCode,
                'sku' => $itemCode,
                'hsn_code' => $hsnCode,
                'description' => $categoryName,
                'price' => 0,
                'stock_quantity' => 0,
                'unit_id' => null,
                'vendor_id' => null,
                'product_category_id' => $categoryId,
                'status' => 'active',
                'category' => $categoryName,
                'image' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Chunked upsert on 'sku' keeps this idempotent if run more than once
        // and avoids duplicate-key errors from the 2 repeated item codes in
        // the source sheet.
        collect($productsToInsert)->chunk(500)->each(function ($chunk) {
            Product::upsert(
                $chunk->toArray(),
                ['sku'],
                [
                    'name',
                    'hsn_code',
                    'description',
                    'product_category_id',
                    'category',
                    'updated_at',
                ]
            );
        });

        $this->command->info(count($productsToInsert) . ' products seeded (' . $skippedRows . ' rows skipped).');
    }

    /**
     * Get (or create) the ProductCategory id for a given category name,
     * caching lookups within this seeder run to avoid repeated queries.
     */
    protected function resolveCategoryId(string $categoryName, array &$cache): ?int
    {
        $key = Str::upper($categoryName);

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $category = ProductCategory::firstOrCreate(
            ['name' => $categoryName],
            ['status' => 'active']
        );

        return $cache[$key] = $category->id;
    }
}
