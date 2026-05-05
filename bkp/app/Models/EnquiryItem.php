<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnquiryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'enquiry_id',
        'product_id',
        'quantity',
        'estimated_price',
        'notes'
    ];

    protected $casts = [
        'estimated_price' => 'decimal:2',
    ];

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

