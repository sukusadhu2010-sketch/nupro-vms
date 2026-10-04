<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make the User linkage fully optional for Customers and Vendors
 * (Foundry Management and Sub-Vendor are both rows of the vendors
 * table distinguished by vendor_type).
 *
 * Customers/vendors are now independent business entities and can be
 * created, updated, retrieved and deleted without any User reference.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop the cascade-deleting FK from the base create migration
        // and replace it with a nullable, null-on-delete relationship.
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });
    }
};
