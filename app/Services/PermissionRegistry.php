<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;

/**
 * Central registry of ERP modules and the RBAC permission matrix.
 *
 * Every module exposed to RBAC is declared here. Permissions are stored
 * as rows in the permissions table with module + action columns, so the
 * matrix is fully data-driven and extensible without code changes.
 */
class PermissionRegistry
{
    /**
     * module key => [label, group]
     */
    public const MODULES = [
        'enquiries'        => ['label' => 'Enquiries',        'group' => 'Sales'],
        'quotations'       => ['label' => 'Quotations',       'group' => 'Sales'],
        'quotation-forms'  => ['label' => 'Offer Documents',  'group' => 'Sales'],
        'sales-orders'     => ['label' => 'Sales Orders',     'group' => 'Sales'],
        'customers'        => ['label' => 'Customers',        'group' => 'Sales'],
        'invoices'         => ['label' => 'Invoices',         'group' => 'Accounts'],
        'receipts'         => ['label' => 'Receipts',         'group' => 'Accounts'],
        'vendor-payments'  => ['label' => 'Vendor Payments',  'group' => 'Accounts'],
        'vendors'          => ['label' => 'Vendors',          'group' => 'Purchase'],
        'procurements'     => ['label' => 'Purchase Orders',  'group' => 'Purchase'],
        'inventory-ledger' => ['label' => 'Inventory Ledger', 'group' => 'Stores'],
        'products'         => ['label' => 'Products',         'group' => 'Stores'],
        'users'            => ['label' => 'Users',            'group' => 'Administration'],
        'roles'            => ['label' => 'User Role Master', 'group' => 'Administration'],
        'audit-logs'       => ['label' => 'Audit Log',        'group' => 'Administration'],
        'sd-pbg'           => ['label' => 'SD/PBG Register',  'group' => 'Reports'],
    ];

    /**
     * Permission actions (Permission::ACTIONS).
     */
    public const ACTIONS = ['view', 'create', 'edit', 'delete', 'approve', 'print', 'export'];

    /**
     * Default roles created by the seeder.
     */
    public const DEFAULT_ROLES = [
        ['name' => 'Administrator', 'description' => 'Full system access'],
        ['name' => 'Sales',         'description' => 'CRM, quotations, sales orders'],
        ['name' => 'Purchase',      'description' => 'Vendors, RFQ, purchase orders, GRN'],
        ['name' => 'Accounts',      'description' => 'Invoices, payments, receivables, payables'],
        ['name' => 'Stores',        'description' => 'Inventory, stock ledger, issues'],
        ['name' => 'Production',    'description' => 'Production planning and jobs'],
        ['name' => 'QC',            'description' => 'Quality control and inspection'],
        ['name' => 'Management',    'description' => 'Dashboards and reports'],
        ['name' => 'Project',       'description' => 'Project tracking'],
        ['name' => 'Logistics',     'description' => 'Dispatch and delivery'],
    ];

    /**
     * Default permission matrix per role.
     * module => [actions]
     */
    public const DEFAULT_MATRIX = [
        'Administrator' => null, // null = all actions on all modules
        'Sales' => [
            'enquiries'       => ['view', 'create', 'edit', 'print', 'export'],
            'quotations'      => ['view', 'create', 'edit', 'print', 'export'],
            'quotation-forms' => ['view', 'create', 'edit', 'print'],
            'sales-orders'    => ['view', 'create', 'edit', 'print', 'export'],
            'customers'       => ['view', 'create', 'edit', 'export'],
            'invoices'        => ['view'],
        ],
        'Purchase' => [
            'vendors'      => ['view', 'create', 'edit', 'export'],
            'procurements' => ['view', 'create', 'edit', 'print', 'export'],
            'products'     => ['view'],
            'customers'    => ['view'],
        ],
        'Accounts' => [
            'invoices'        => ['view', 'create', 'edit', 'approve', 'print', 'export'],
            'receipts'        => ['view', 'create', 'edit', 'export'],
            'vendor-payments' => ['view', 'create', 'edit', 'export'],
            'procurements'    => ['view'],
            'sales-orders'    => ['view'],
        ],
        'Stores' => [
            'inventory-ledger' => ['view', 'create', 'export'],
            'products'         => ['view', 'edit', 'export'],
            'procurements'     => ['view'],
        ],
        'Production' => [
            'sales-orders'     => ['view'],
            'inventory-ledger' => ['view'],
            'products'         => ['view'],
        ],
        'QC' => [
            'procurements'     => ['view'],
            'inventory-ledger' => ['view'],
        ],
        'Management' => [
            'sales-orders'     => ['view', 'export'],
            'procurements'     => ['view', 'export'],
            'invoices'         => ['view', 'export'],
            'receipts'         => ['view', 'export'],
            'vendor-payments'  => ['view', 'export'],
            'inventory-ledger' => ['view', 'export'],
        ],
        'Project' => [
            'sales-orders'     => ['view'],
            'inventory-ledger' => ['view'],
        ],
        'Logistics' => [
            'sales-orders'     => ['view'],
            'inventory-ledger' => ['view'],
            'procurements'     => ['view'],
        ],
    ];

    /**
     * Ensure the permissions table contains module.action rows for every
     * module/action pair, and create the default roles + matrix.
     */
    public static function seed(): void
    {
        // 1. Permissions — idempotent, keyed on module+action
        foreach (array_keys(self::MODULES) as $module) {
            foreach (self::ACTIONS as $i => $action) {
                Permission::firstOrCreate(
                    ['module' => $module, 'action' => $action],
                    [
                        'name' => "{$module}.{$action}",
                        'label' => self::MODULES[$module]['label'] . ' / ' . ucfirst($action),
                        'guard_name' => 'web',
                        'sort_order' => $i,
                    ]
                );
            }
        }

        // 2. Default roles
        foreach (self::DEFAULT_ROLES as $i => $r) {
            Role::firstOrCreate(
                ['name' => $r['name']],
                [
                    'description' => $r['description'],
                    'is_active' => true,
                    'sort_order' => $i,
                    'guard_name' => 'web',
                ]
            );
        }

        // 3. Default matrix — only attach when the role has no permissions yet,
        //    so admin customisations are never overwritten.
        foreach (self::DEFAULT_MATRIX as $roleName => $matrix) {
            $role = Role::where('name', $roleName)->first();
            if (! $role || $role->permissions()->exists()) {
                continue;
            }

            if ($matrix === null) {
                // Administrator: everything
                $role->permissions()->sync(Permission::all()->pluck('id'));
                continue;
            }

            $ids = [];
            foreach ($matrix as $module => $actions) {
                $ids = array_merge($ids, Permission::where('module', $module)
                    ->whereIn('action', $actions)
                    ->pluck('id')
                    ->all());
            }
            $role->permissions()->sync(array_unique($ids));
        }
    }
}
