<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationForm extends Model
{
    protected $table = 'quotation_forms';

    protected $fillable = [
        'company_name', 'certification', 'ref_no', 'form_date', 'title',
        'to_company', 'to_address', 'project_details',
        'contact_person', 'contact_designation',
        'acknowledgement', 'product_spec', 'description_entries',
        'items', 'grand_total',
        'delivery_terms', 'igst_percent', 'sgst_percent', 'cgst_percent',
        'payment_terms', 'inspection_vendor_scope', 'inspection_third_party_scope',
        'warranty_terms',
        'notes', 'closing_statement', 'signatory_company', 'signatory_designation',
        'enquiry_id', 'status',
    ];

    protected $casts = [
        'form_date' => 'date',
        'description_entries' => 'array',
        'items' => 'array',
        'grand_total' => 'decimal:2',
        'igst_percent' => 'decimal:2',
        'sgst_percent' => 'decimal:2',
        'cgst_percent' => 'decimal:2',
    ];

    /**
     * Generate the next reference number in the format NES/YY-YY/QTN-XXX.
     * YY-YY uses the Indian fiscal year (Apr-Mar) of the given date.
     */
    public static function generateRefNo($date = null): string
    {
        $date = $date ? \Carbon\Carbon::parse($date) : now();

        // Indian fiscal year: April to March
        $startYear = $date->month >= 4 ? $date->year : $date->year - 1;
        $fiscal = sprintf('%02d-%02d', $startYear % 100, ($startYear + 1) % 100);

        $count = self::whereYear('created_at', '>=', $startYear)->count();
        $seq = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        // Ensure uniqueness even after deletions
        while (self::where('ref_no', "NES/{$fiscal}/QTN-{$seq}")->exists()) {
            $seq = str_pad(((int) $seq) + 1, 3, '0', STR_PAD_LEFT);
        }

        return "NES/{$fiscal}/QTN-{$seq}";
    }

    /** Recalculate grand total from items. */
    public function recalculateTotal(): float
    {
        $total = 0;
        foreach ($this->items ?? [] as $item) {
            $total += (float) ($item['total_price'] ?? 0);
        }
        return $total;
    }

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class);
    }
}
