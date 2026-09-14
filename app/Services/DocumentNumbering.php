<?php

namespace App\Services;

use App\Models\FinancialYear;
use App\Models\OrganizationSetting;
use App\Models\QuotationCounter;
use Illuminate\Support\Facades\DB;

class DocumentNumbering
{
    /**
     * Atomically reserve the next serial for a document type in the active FY.
     * Thread-safe: row-level lock (SELECT ... FOR UPDATE) inside a transaction.
     *
     * @param  string  $type  'QTN' or 'ENQ'
     * @return string e.g. NES/26-27/QTN-00001
     */
    public static function nextNumber(string $type, ?FinancialYear $fy = null): string
    {
        $fy = $fy ?? FinancialYear::active();
        if (!$fy) {
            throw new \RuntimeException('No active financial year. Please activate one in Financial Year Settings first.');
        }

        $org = OrganizationSetting::current();
        $abbr = strtoupper($org->abbreviation ?: 'ORG');

        return DB::transaction(function () use ($fy, $type, $abbr) {
            $counter = QuotationCounter::where('financial_year_id', $fy->id)
                ->where('document_type', $type)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                // insertWithUniqueAndRowLocking avoids race on first insert of a FY
                $counter = QuotationCounter::firstOrCreate(
                    ['financial_year_id' => $fy->id, 'document_type' => $type],
                    ['last_serial' => 0]
                );
                $counter = QuotationCounter::where('financial_year_id', $fy->id)
                    ->where('document_type', $type)->lockForUpdate()->first();
            }

            $counter->last_serial += 1;
            $counter->save();

            return sprintf('%s/%s/%s-%05d', $abbr, $fy->fy_short_code, $type, $counter->last_serial);
        });
    }

    /** Preview the next number WITHOUT consuming it. */
    public static function peek(string $type): ?string
    {
        $fy = FinancialYear::active();
        if (!$fy) {
            return null;
        }
        $abbr = strtoupper(OrganizationSetting::current()->abbreviation ?: 'ORG');
        $last = (int) (DB::table('quotation_counters')
            ->where('financial_year_id', $fy->id)
            ->where('document_type', $type)
            ->value('last_serial') ?? 0);

        return sprintf('%s/%s/%s-%05d', $abbr, $fy->fy_short_code, $type, $last + 1);
    }
}
