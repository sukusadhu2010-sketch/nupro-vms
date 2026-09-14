<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'hsn_code',
        'description',
        'price',
        'stock_quantity',
        'unit_id',
        'vendor_id',
        'product_category_id',
        'status',
        'category',
        'image'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Human-friendly Product Code (auto-generated from Category + Name).
     */
    public function getProductCodeAttribute()
    {
        return $this->sku;
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function enquiryItems()
    {
        return $this->hasMany(EnquiryItem::class);
    }

    public function getStatusBadgeAttribute()
    {
        $config = [
            'active' => 'bg-success',
            'out_of_stock' => 'bg-warning',
            'draft' => 'bg-secondary',
            'inactive' => 'bg-danger'
        ];

        return $config[$this->status] ?? 'bg-secondary';
    }
}


