<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationCounter extends Model
{
    protected $fillable = ['financial_year_id', 'document_type', 'last_serial'];

    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class);
    }
}
