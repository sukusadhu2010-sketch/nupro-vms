<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Roles relationship.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Permissions relationship.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Business-entity links (Customer / Vendor) are handled through explicit
     * mapping tables so that User creation stays fully independent.
     * These accessors return the linked entity if one exists, else null.
     */
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    /**
     * Check if user has role.
     */
    public function hasRole($role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if user has any role.
     */
    public function hasAnyRole(...$roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if user has permission.
     *
     * Supports legacy string permission ("name" column) as well as the
     * new module/action pair, e.g. hasPermission('invoices', 'view').
     * Administrators always pass.
     */
    public function hasPermission($permission, ?string $action = null): bool
    {
        if ($this->hasAnyRole('Administrator', 'admin', 'Super Admin')) {
            return true;
        }

        if ($action === null && str_contains($permission, '.')) {
            [$permission, $action] = explode('.', $permission, 2);
        } elseif ($action === null) {
            $action = 'view';
        }

        return \App\Services\PermissionService::userCan($this, $permission, $action);
    }

    /**
     * All distinct permissions granted through the user's roles,
     * cached per request for menu rendering.
     */
    public function getRolePermissions(): \Illuminate\Support\Collection
    {
        static $cache = null;

        if ($cache === null) {
            $cache = Permission::query()
                ->whereHas('roles.users', fn ($q) => $q->where('users.id', $this->id))
                ->get();
        }

        return $cache;
    }

    /**
     * Can the user perform $action on $module?
     */
    public function canAccess(string $module, string $action = 'view'): bool
    {
        return $this->hasPermission($module, $action);
    }
}

