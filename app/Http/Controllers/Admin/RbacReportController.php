<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\PermissionRegistry;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class RbacReportController extends Controller
{
    /**
     * Report 1: Role Wise Users
     */
    public function roleUsers(Request $request)
    {
        $roles = Role::with(['users' => function ($q) {
            $q->orderBy('name');
        }])->withCount('permissions')->orderBy('name')->get();

        return view('admin.reports.rbac.role-users', compact('roles'));
    }

    /**
     * Report 2: Permission Matrix
     */
    public function permissionMatrix(Request $request)
    {
        $roles = Role::active()->orderBy('name')->get();
        $permissions = Permission::orderBy('module')->orderBy('sort_order')->get()->groupBy('module');

        return view('admin.reports.rbac.permission-matrix', compact('roles', 'permissions'));
    }

    /**
     * Report 3: User Access Report
     */
    public function userAccess(Request $request)
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $selectedUser = null;
        $modules = [];

        if ($request->filled('user_id')) {
            $selectedUser = User::with('roles.permissions')->find($request->user_id);
            if ($selectedUser) {
                // Group all modules
                foreach (PermissionRegistry::MODULES as $modKey => $modInfo) {
                    $actions = [];
                    foreach (PermissionRegistry::ACTIONS as $action) {
                        $actions[$action] = PermissionService::userCan($selectedUser, $modKey, $action);
                    }
                    $modules[$modKey] = [
                        'label' => $modInfo['label'],
                        'group' => $modInfo['group'],
                        'actions' => $actions,
                    ];
                }
            }
        }

        return view('admin.reports.rbac.user-access', compact('users', 'selectedUser', 'modules'));
    }
}

