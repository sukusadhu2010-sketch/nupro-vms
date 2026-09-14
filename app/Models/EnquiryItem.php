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
        'unit_price',
        'total_price',
        'estimated_price',
        'notes',
        'payment_methods',
        'moc',
        'mfg_spec',
        'trim',
        'operation',
        'end_connection',
        'rating',
        'media',
        'remarks',
    ];

    protected $casts = [
        'estimated_price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'payment_methods' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($item) {
            $item->total_price = $item->quantity * $item->unit_price;
            $item->estimated_price = $item->estimated_price ?? $item->unit_price;
        });
    }

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

