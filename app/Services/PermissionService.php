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
     * Administrators (a role named exactly 'Administrator') always pass.
     */
    public static function userCan(User $user, string $module, string $action): bool
    {
        $key = "rbac.{$user->id}.{$module}.{$action}";

        return Cache::remember($key, now()->addMinutes(10), function () use ($user, $module, $action) {
            foreach ($user->roles as $role) {
                if ($role->name === 'Administrator' || $role->hasPermission($module, $action)) {
                    return true;
                }
            }
            return false;
        });
    }
}
