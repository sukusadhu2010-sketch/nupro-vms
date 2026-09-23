<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the missing user_id column to the customers table.
     * Backfills user_id by matching customer email to users.email.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('customers', 'user_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id');
                $table->foreign('user_id', 'customers_user_id_fk')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });

            // Backfill: link customer to user with the same email
            DB::statement(
                'UPDATE customers c INNER JOIN users u ON u.email = c.email SET c.user_id = u.id'
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'user_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
