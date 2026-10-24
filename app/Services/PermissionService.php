<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    /**
     * Every permission row in the matrix.
     */
    public static function allPermissions(): Collection
    {
        return Permission::orderBy('module')->orderBy('action')->get();
    }

    /**
     * module => action => [permission_id => bool]
     */
    public static function matrix(): array
    {
        $permissions = self::allPermissions();
        $roles = Role::orderBy('sort_order')->get();

        $matrix = [];
        foreach ($permissions as $p) {
            $matrix[$p->module][$p->action] = [];
        }
        foreach ($roles as $role) {
            $ids = $role->permissions->pluck('id')->flip();
            foreach ($permissions as $p) {
                $matrix[$p->module][$p->action][$role->id] = $ids->has($p->id);
            }
        }

        return [
            'roles' => $roles,
            'matrix' => $matrix,
        ];
    }

    /**
     * Does a user have a specific module.action permission?
     * Administrators (Administrator, admin, super admin) always pass.
     */
    public static function userCan(User $user, string $module, string $action): bool
    {
        $key = "rbac.{$user->id}.{$module}.{$action}";

        return Cache::remember($key, now()->addMinutes(10), function () use ($user, $module, $action) {
            foreach ($user->roles as $role) {
                $roleName = strtolower($role->name);
                if (in_array($roleName, ['admin', 'administrator', 'super admin', 'super-admin'], true) || $role->hasPermission($module, $action)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Clear cached permissions for a user or entirely.
     */
    public static function clearCache(?User $user = null): void
    {
        if ($user) {
            foreach (array_keys(PermissionRegistry::MODULES) as $module) {
                foreach (PermissionRegistry::ACTIONS as $action) {
                    Cache::forget("rbac.{$user->id}.{$module}.{$action}");
                }
            }
        } else {
            Cache::flush();
        }
    }
}
