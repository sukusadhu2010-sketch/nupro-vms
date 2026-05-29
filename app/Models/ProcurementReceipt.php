<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_id',
        'procurement_item_id',
        'quantity_received',
        'received_at',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'quantity_received' => 'integer',
        'received_at' => 'datetime',
    ];

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    public function procurementItem(): BelongsTo
    {
        return $this->belongsTo(ProcurementItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

