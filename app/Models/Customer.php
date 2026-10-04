<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
       'name', 'email', 'phone', 'gst_no', 'address', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}

