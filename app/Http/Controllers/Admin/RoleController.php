<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * Roles listing page (DataTable).
     */
    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * DataTables feed of roles.
     */
    public function data(Request $request)
    {
        $query = Role::with('createdBy')->withCount(['permissions', 'users']);

        return DataTables::of($query)
            ->addColumn('status_html', fn (Role $r) => $r->is_active
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>')
            ->addColumn('counts', fn (Role $r) => "{$r->permissions_count} perms / {$r->users_count} users")
            ->addColumn('actions_html', function (Role $r) {
                $canEdit = PermissionService::userCan(Auth::user(), 'roles', 'edit');
                $canDelete = PermissionService::userCan(Auth::user(), 'roles', 'delete');

                $btns = '';
                if ($canEdit) {
                    $btns .= '<a href="'.route('roles.edit', $r).'" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>';
                    $btns .= '<button type="button" class="btn btn-sm btn-outline-'.($r->is_active ? 'warning' : 'success').' me-1" onclick="toggleStatus('.$r->id.')"><i class="fas fa-'.($r->is_active ? 'eye-slash' : 'eye').'"></i></button>';
                }
                if ($canDelete) {
                    $btns .= '<button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRole('.$r->id.')"><i class="fas fa-trash"></i></button>';
                }
                return $btns ?: '<span class="text-muted small">No actions</span>';
            })
            ->rawColumns(['status_html', 'actions_html'])
            ->make(true);
    }

    /**
     * Show create form with module/action matrix.
     */
    public function create()
    {
        $modules = $this->moduleMatrix();
        return view('admin.roles.form', compact('modules'));
    }

    /**
     * Store a new role with its permissions.
     */
    public function store(Request $request)
    {
        $validated = $this->validateRole($request);

        DB::transaction(function () use ($request, $validated) {
            $role = Role::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'created_by' => Auth::id(),
            ]);

            $this->syncPermissions($role, $request->input('permissions', []));
        });

        return redirect()->route('roles.index')->with('success', 'Role "' . $validated['name'] . '" created successfully.');
    }

    /**
     * Show edit form with current role permissions pre-checked.
     */
    public function edit(Role $role)
    {
        $role->load('permissions');
        $modules = $this->moduleMatrix();

        return view('admin.roles.form', compact('role', 'modules'));
    }

    /**
     * Update role details and permission matrix.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $this->validateRole($request, $role);

        DB::transaction(function () use ($request, $role, $validated) {
            $role->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->syncPermissions($role, $request->input('permissions', []));
        });

        return redirect()->route('roles.index')->with('success', 'Role "' . $role->name . '" updated successfully.');
    }

    /**
     * Toggle role active status (AJAX).
     */
    public function toggleStatus(Role $role)
    {
        $role->update(['is_active' => !$role->is_active]);
        return response()->json(['status' => true, 'is_active' => $role->is_active]);
    }

    /**
     * Soft delete a role.
     */
    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return response()->json(['status' => false, 'message' => 'Cannot delete: role is assigned to users.'], 422);
        }
        $role->delete();
        return response()->json(['status' => true, 'message' => 'Role deleted.']);
    }

    /**
     * Users assigned to a role (JSON, for detail drawer).
     */
    public function users(Role $role)
    {
        return response()->json([
            'users' => $role->users()->select('users.id', 'users.name', 'users.email')->get(),
        ]);
    }

    protected function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role?->id)],
            'description' => 'nullable|string|max:500',
        ]);
    }

    protected function syncPermissions(Role $role, array $granted): void
    {
        // $granted contains permission IDs from the matrix checkboxes
        $ids = array_map('intval', $granted);
        $valid = Permission::whereIn('id', $ids)->pluck('id')->all();

        $role->permissions()->sync($valid);
    }

    /**
     * module => ['label' => ..., 'actions' => [...]] built from the permissions table.
     */
    protected function moduleMatrix(): array
    {
        return Permission::select('module')->distinct()->orderBy('module')->pluck('module')
            ->mapWithKeys(fn ($m) => [
                $m => [
                    'label' => str_replace('-', ' ', ucfirst($m)),
                    'actions' => Permission::where('module', $m)->distinct()->orderBy('action')->pluck('action')->all(),
                ],
            ])
            ->all();
    }
}
