<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidates the duplicate status columns on roles:
 * - 2026_10_20 added is_active
 * - 2026_10_21 added status (redundant)
 * The codebase uses is_active, so status is removed here.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'status')) {
            Schema::table('roles', function (Blueprint $t) {
                $t->dropColumn('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('roles') && !Schema::hasColumn('roles', 'status')) {
            Schema::table('roles', function (Blueprint $t) {
                $t->boolean('status')->default(true);
            });
        }
    }
};
