<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'customer_information')) {
                $table->text('customer_information')->nullable()->after('acknowledgement');
            }
            if (!Schema::hasColumn('quotations', 'kind_attention')) {
                $table->string('kind_attention')->nullable()->after('customer_information');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (Schema::hasColumn('quotations', 'customer_information')) {
                $table->dropColumn('customer_information');
            }
            if (Schema::hasColumn('quotations', 'kind_attention')) {
                $table->dropColumn('kind_attention');
            }
        });
    }
};
