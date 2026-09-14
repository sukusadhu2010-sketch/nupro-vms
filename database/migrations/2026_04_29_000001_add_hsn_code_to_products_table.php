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
        Schema::table('products', function (Blueprint $table) {
            // HSN Code intentionally has NO unique constraint (duplicates allowed)
            if (!Schema::hasColumn('products', 'hsn_code')) {
                $table->string('hsn_code')->nullable()->after('sku');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'hsn_code')) {
                $table->dropColumn('hsn_code');
            }
        });
    }
};
