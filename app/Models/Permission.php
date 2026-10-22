<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    public const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve', 'print', 'export'];

    protected $fillable = ['module', 'action', 'label', 'description', 'sort_order', 'name', 'guard_name'];

    /**
     * Safely derive module/action from the legacy `name` column
     * (e.g. "view-users" -> module=users, action=view) when missing.
     */
    public function getModuleAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }
        $parts = explode('-', $this->name ?? '', 2);
        $candidate = $parts[0] ?? '';
        return in_array($candidate, self::ACTIONS, true) ? ($parts[1] ?? '') : ($this->name ?? '');
    }

    public function getActionAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }
        $parts = explode('-', $this->name ?? '', 2);
        $candidate = $parts[0] ?? '';
        return in_array($candidate, self::ACTIONS, true) ? $candidate : 'view';
    }

    public function getLabelAttribute($value): string
    {
        return $value ?: ucfirst($this->action) . ' ' . str_replace('-', ' ', $this->module);
    }

    public function getDescriptionAttribute($value): ?string
    {
        return $value ?: ucfirst($this->action) . ' ' . str_replace('-', ' ', $this->module);
    }

    /** Canonical permission key/slug used for RBAC checks. */
    public function getKeyAttribute(): string
    {
        return $this->module . '.' . $this->action;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /** Number of roles currently assigned to this permission. */
    public function getRolesCountAttribute(): int
    {
        return $this->roles()->count();
    }
}

