<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the missing user_id column to the vendors table.
     * Backfills user_id by matching vendor email to users.email.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('vendors', 'user_id')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete();
            });

            // Backfill: link vendor to user with the same email
            DB::statement(
                'UPDATE vendors v INNER JOIN users u ON u.email = v.email SET v.user_id = u.id'
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vendors', 'user_id')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
