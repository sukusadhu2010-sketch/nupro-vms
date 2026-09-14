<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'gst_no')) {
                // Nullable in DB so legacy rows keep working;
                // enforced as REQUIRED at the application/form level.
                $table->string('gst_no', 20)->nullable()->after('phone');
            }

            // Company Name removed from customer management entirely
            if (Schema::hasColumn('customers', 'company')) {
                $table->dropColumn('company');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'gst_no')) {
                $table->dropColumn('gst_no');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'company')) {
                $table->string('company')->nullable()->after('name');
            }
        });
    }
};
