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
        Schema::table('vendors', function (Blueprint $table) {
            // "Vendor Type" — FOUNDRY or SUB VENDOR.
            // Nullable in DB so legacy rows keep working;
            // enforced as REQUIRED at the application/form level.
            if (!Schema::hasColumn('vendors', 'vendor_type')) {
                $table->string('vendor_type', 20)->nullable()->after('particulars');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'vendor_type')) {
                $table->dropColumn('vendor_type');
            }
        });
    }
};
