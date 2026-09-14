<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'particulars', 'vendor_type', 'address', 'status', 'gst_no'
    ];

    public const VENDOR_TYPES = ['FOUNDRY', 'SUB VENDOR'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

