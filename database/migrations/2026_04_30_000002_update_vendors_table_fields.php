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
            // 1. Replace "Company Name" (company) with "Particulars"
            if (Schema::hasColumn('vendors', 'company')) {
                $table->renameColumn('company', 'particulars');
            }

            // 5. Remove "Specialization" entirely
            if (Schema::hasColumn('vendors', 'specialization')) {
                $table->dropColumn('specialization');
            }

            // 4. Add "GST No" — nullable in DB so legacy rows keep working;
            //    enforced as REQUIRED at the application/form level.
            if (!Schema::hasColumn('vendors', 'gst_no')) {
                $table->string('gst_no', 20)->nullable()->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'particulars')) {
                $table->renameColumn('particulars', 'company');
            }

            if (!Schema::hasColumn('vendors', 'specialization')) {
                $table->string('specialization')->nullable();
            }

            if (Schema::hasColumn('vendors', 'gst_no')) {
                $table->dropColumn('gst_no');
            }
        });
    }
};
