<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend the existing RBAC tables with the fields required by the
     * User Role Master module (description, status, audit info, sorting)
     * and add the "sort_order" column used by the role CRUD screens.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
            $table->boolean('is_active')->default(true)->after('description');
            $table->unsignedTinyInteger('sort_order')->default(0)->after('is_active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('sort_order');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('module')->nullable()->after('name');
            $table->string('action')->nullable()->after('module');
            $table->string('label')->nullable()->after('action');
            $table->unsignedTinyInteger('sort_order')->default(0)->after('label');
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['module', 'action', 'label', 'sort_order']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['description', 'is_active', 'sort_order']);
        });
    }
};
