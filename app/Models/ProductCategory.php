<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getStatusBadgeAttribute()
    {
        $config = [
            'active' => 'bg-success',
            'inactive' => 'bg-danger',
        ];

        return $config[$this->status] ?? 'bg-secondary';
    }
}
