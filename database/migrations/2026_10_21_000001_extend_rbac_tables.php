<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $t) {
                $t->id();
                $t->string('name')->unique();
                $t->string('description')->nullable();
                $t->boolean('status')->default(true);
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamps();
            });
        } else {
            Schema::table('roles', function (Blueprint $t) {
                if (!Schema::hasColumn('roles', 'description')) $t->string('description')->nullable();
                if (!Schema::hasColumn('roles', 'status')) $t->boolean('status')->default(true);
                if (!Schema::hasColumn('roles', 'created_by')) $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $t) {
                $t->id();
                $t->string('module')->index();
                $t->string('action')->index();
                $t->foreignId('role_id')->nullable()->constrained()->cascadeOnDelete();
                $t->timestamps();
                $t->unique(['role_id', 'module', 'action']);
            });
        } else {
            Schema::table('permissions', function (Blueprint $t) {
                if (!Schema::hasColumn('permissions', 'role_id')) {
                    $t->foreignId('role_id')->nullable()->constrained()->cascadeOnDelete();
                }
            });
        }

        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $t) {
                $t->foreignId('role_id')->constrained()->cascadeOnDelete();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $t->primary(['role_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
