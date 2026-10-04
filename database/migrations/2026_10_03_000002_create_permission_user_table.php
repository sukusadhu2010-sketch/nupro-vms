<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the permission_user pivot table and add the permission_id
 * column to role_user so both pivot tables exist for the
 * Role/Permission belongsToMany relationships.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permission_user')) {
            Schema::create('permission_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('role_user', 'permission_id')) {
            Schema::table('role_user', function (Blueprint $table) {
                $table->foreignId('permission_id')->nullable()->after('role_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');

        if (Schema::hasColumn('role_user', 'permission_id')) {
            Schema::table('role_user', function (Blueprint $table) {
                $table->dropColumn('permission_id');
            });
        }
    }
};
