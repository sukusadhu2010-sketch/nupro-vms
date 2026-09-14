<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FinancialYear extends Model
{
    protected $fillable = [
        'fy_label', 'fy_short_code', 'start_date', 'end_date', 'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /** Auto-suggest label/short code from a date (Indian FY: Apr 1 - Mar 31). */
    public static function suggestFromDate(\DateTimeInterface $date): array
    {
        $y = (int) $date->format('Y');
        $m = (int) $date->format('n');
        $startYear = $m >= 4 ? $y : $y - 1;
        $endYear = $startYear + 1;
        return [
            'fy_label' => $startYear . '-' . substr((string) $endYear, -2),
            'fy_short_code' => substr((string) $startYear, -2) . '-' . substr((string) $endYear, -2),
        ];
    }

    /** Activate this FY and deactivate all others atomically. */
    public function activate(): void
    {
        DB::transaction(function () {
            static::where('is_active', true)->update(['is_active' => false]);
            $this->update(['is_active' => true]);
        });
    }

    public function hasDocuments(): bool
    {
        return Quotation::where('financial_year_id', $this->id)->exists()
            || Enquiry::where('financial_year_id', $this->id)->exists();
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function enquiries()
    {
        return $this->hasMany(Enquiry::class);
    }
}
