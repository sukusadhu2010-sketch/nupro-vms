<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrganizationSetting extends Model
{
    protected $table = 'organization_settings';

    protected $fillable = [
        'name', 'abbreviation', 'logo_path',
        'address_line1', 'address_line2', 'city', 'state', 'pincode', 'country',
        'gstin', 'contact_number', 'email', 'website', 'bank_details',
    ];

    /** Single-record convenience accessor. */
    public static function current(): self
    {
        return static::first() ?? new static(['name' => 'VMS Pro', 'abbreviation' => 'VMS']);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::url($this->logo_path) : null;
    }
}
