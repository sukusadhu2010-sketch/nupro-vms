<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
        'priority',
        'message',
        'attachments',
        'total_amount'
    ];

    protected $casts = [
        'attachments' => 'array',
        'total_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(EnquiryItem::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class)->orderBy('version', 'desc');
    }

    public function acceptedQuotation()
    {
        return $this->hasOne(Quotation::class)->where('status', 'accepted');
    }

    public function getStatusBadgeAttribute()
    {
        $config = [
            'pending' => 'bg-warning',
            'quoted' => 'bg-info',
            'closed' => 'bg-secondary'
        ];
        return $config[$this->status] ?? 'bg-secondary';
    }

    public function getPriorityBadgeAttribute()
    {
        $config = [
            'low' => 'bg-success',
            'medium' => 'bg-info', 
            'high' => 'bg-danger'
        ];
        return $config[$this->priority] ?? 'bg-secondary';
    }

    public function getItemsCountAttribute()
    {
        return $this->items->count();
    }

    public function getFormattedItemsAttribute()
    {
        return $this->items->map(function ($item) {
            return $item->product->name . ' (Qty: ' . $item->quantity . ')';
        })->implode(', ');
    }

    public function getTotalAmountAttribute()
    {
        if (app()->runningInConsole()) {
            return $this->attributes['total_amount'] ?? 0;
        }
        return $this->items->sum(function ($item) {
            $price = $item->estimated_price ?? $item->product->price;
            return $item->quantity * $price;
        });
    }
}
