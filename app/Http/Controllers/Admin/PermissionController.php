<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function index()
    {
        $grouped = Permission::orderBy('module')->orderBy('action')
            ->get()
            ->groupBy('module');

        return view('admin.permissions.index', compact('grouped'));
    }

    public function data()
    {
        $query = Permission::query();

        return DataTables::of($query)
            ->addColumn('label_html', fn (Permission $p) =>
                '<strong>'.e($p->module).'</strong>'
                .'<br><span class="text-muted small">'.e($p->action).' — '.e($p->description ?? '').'</span>'
            )
            ->addColumn('roles_html', fn (Permission $p) =>
                $p->roles->map(fn ($r) => '<span class="badge bg-info me-1">'.e($r->name).'</span>')->implode(' ')
                ?: '<span class="text-muted small">—</span>'
            )
            ->addColumn('actions_html', function (Permission $p) {
                return '<a href="'.route('permissions.edit', $p).'" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></a>'
                    .'<button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePermission('.$p->id.')"><i class="fas fa-trash"></i></button>';
            })
            ->rawColumns(['label_html', 'roles_html', 'actions_html'])
            ->make(true);
    }

    public function create()
    {
        $modules = Permission::select('module')->distinct()->orderBy('module')->pluck('module')->all();
        $actions = Permission::ACTIONS;
        $permission = new Permission();
        return view('admin.permissions.form', compact('permission', 'modules', 'actions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string|max:100',
            'action' => 'required|string|in:view,create,edit,delete,approve,print,export',
            'label'  => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $name = $validated['module'].'.'.$validated['action'];

        $exists = Permission::where('module', $validated['module'])->where('action', $validated['action'])->exists();
        if ($exists) {
            return back()->withErrors(['action' => 'This module/action permission already exists.'])->withInput();
        }

        Permission::create([
            'module' => $validated['module'],
            'action' => $validated['action'],
            'label'  => $validated['label'] ?? ucfirst($validated['action']).' '.ucfirst($validated['module']),
            'description' => $validated['description'] ?? null,
            'name'   => $name,
            'sort_order' => 0,
        ]);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission)
    {
        $modules = Permission::select('module')->distinct()->orderBy('module')->pluck('module')->all();
        $actions = Permission::ACTIONS;
        return view('admin.permissions.form', compact('permission', 'modules', 'actions'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'module' => 'required|string|max:100',
            'action' => 'required|string|in:view,create,edit,delete,approve,print,export',
            'label'  => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $permission->update([
            'module' => $validated['module'],
            'action' => $validated['action'],
            'label'  => $validated['label'] ?? ucfirst($validated['action']).' '.ucfirst($validated['module']),
            'description' => $validated['description'] ?? null,
            'name'   => $validated['module'].'.'.$validated['action'],
        ]);

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        // Detach from all roles before deleting
        $permission->roles()->detach();
        $permission->delete();

        return response()->json(['status' => true, 'message' => 'Permission deleted.']);
    }
}
