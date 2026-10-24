<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasRecordLevelSecurity
{
    /**
     * Scope a query to only include records accessible to the given or authenticated user.
     * Administrators and Management see all records.
     * Other users are restricted to records created by them (or department if expanded).
     */
    public function scopeForUser(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return $query;
        }

        if ($user->hasAnyRole('Administrator', 'admin', 'Super Admin', 'Management')) {
            return $query;
        }

        // If the table has a created_by column, restrict to user's own records
        return $query->where($this->getTable() . '.created_by', $user->id);
    }
}

