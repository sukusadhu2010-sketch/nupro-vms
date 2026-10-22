<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Backfills the module / action / description / label columns from the
 * legacy `name` values (e.g. "view-users" -> module=users, action=view)
 * so the Permission Management page can display them, and adds a unique
 * index on (module, action) to prevent duplicates going forward.
 */
return new class extends Migration
{
    private const KNOWN_ACTIONS = ['view', 'create', 'edit', 'delete', 'approve', 'print', 'export'];

    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        // Ensure the description column exists
        if (!Schema::hasColumn('permissions', 'description')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('description')->nullable()->after('label');
            });
        }

        foreach (DB::table('permissions')->get() as $row) {
            $module = $row->module;
            $action = $row->action;

            // Derive from name when module/action are missing
            if (empty($module) || empty($action)) {
                $name = $row->name ?? '';
                $parts = explode('-', $name, 2);
                $guessedAction = $parts[0] ?? '';
                $guessedModule = $parts[1] ?? $name;

                if (in_array($guessedAction, self::KNOWN_ACTIONS, true)) {
                    $module = $module ?: $guessedModule;
                    $action = $action ?: $guessedAction;
                } else {
                    $module = $module ?: $guessedModule;
                    $action = $action ?: 'view';
                }
            }

            DB::table('permissions')->where('id', $row->id)->update([
                'module' => $module,
                'action' => $action,
                'label' => $row->label ?? null,
                'description' => $row->description ?? (ucfirst($action) . ' ' . str_replace('-', ' ', $module)),
                'updated_at' => now(),
            ]);
        }

        // Special case: the legacy "assign-roles" permission maps to roles.assign,
        // not a duplicate of roles.view.
        DB::table('permissions')->where('name', 'assign-roles')->update([
            'module' => 'roles',
            'action' => 'assign',
            'description' => 'Assign roles to users',
            'updated_at' => now(),
        ]);

        // Enforce module + action uniqueness, if the schema allows it
        try {
            Schema::table('permissions', function (Blueprint $table) {
                $table->unique(['module', 'action'], 'permissions_module_action_unique');
            });
        } catch (\Throwable $e) {
            // index may already exist
        }
    }

    public function down(): void
    {
        try {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropUnique('permissions_module_action_unique');
            });
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
