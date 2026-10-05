<?php

namespace App\Services;

use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

class JobNumberService
{
    /**
     * Generate the next unique Job Number in the format
     * JOB<FinancialYear>-<SequenceNumber>, e.g. JOB2627-0012.
     *
     * The financial year is the 2-digit ending years of the current
     * Indian FY (April–March): FY 2026-27 => "2627".
     *
     * Uses a pessimistic lock so concurrent creation cannot duplicate
     * the sequence number.
     */
    public function generate(): string
    {
        return DB::transaction(function () {
            $fy = $this->financialYear();
            $prefix = 'JOB' . $fy . '-';

            $last = SalesOrder::where('job_number', 'like', $prefix . '%')
                ->orderByDesc('job_number')
                ->lockForUpdate()
                ->first();

            $next = $last
                ? ((int) substr($last->job_number, strlen($prefix)) + 1)
                : 1;

            // Ensure uniqueness even if legacy rows hold out-of-order numbers.
            $number = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            while (SalesOrder::where('job_number', $number)->exists()) {
                $next++;
                $number = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            }

            return $number;
        });
    }

    /**
     * Current financial year label: "2627" for FY 2026-27 (April start).
     */
    public function financialYear(?\DateTimeInterface $date = null): string
    {
        $date ??= now();
        $year = (int) $date->format('Y');
        $month = (int) $date->format('n');

        if ($month < 4) {
            $start = $year - 1;
        } else {
            $start = $year;
        }

        return substr((string) $start, -2) . substr((string) ($start + 1), -2);
    }

    /**
     * Generate the next unique Sales Order Number in the format
     * SO-<Year>-<SequenceNumber>, e.g. SO-2026-0019.
     *
     * Uses a pessimistic lock so concurrent creation cannot duplicate
     * the sequence number (fixes duplicate 'SO-YYYY-####' errors that
     * occurred when the number was derived from the quotation ID).
     */
    public function generateOrderNumber(): string
    {
        return DB::transaction(function () {
            $prefix = 'SO-' . now()->format('Y') . '-';

            $max = (int) SalesOrder::where('order_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->max('order_number');

            $next = $max ? ((int) substr($max, strlen($prefix)) + 1) : 1;

            // Ensure uniqueness even if legacy rows hold out-of-order numbers.
            $number = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            while (SalesOrder::where('order_number', $number)->exists()) {
                $next++;
                $number = $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            }

            return $number;
        });
    }
}
