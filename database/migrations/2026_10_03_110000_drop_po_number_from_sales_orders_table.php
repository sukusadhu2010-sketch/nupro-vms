<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Repoint customer_po_number/customer_po_date right after order_number
        // (po_number sat between them) before dropping it.
        if (Schema::hasColumn('sales_orders', 'po_number')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropColumn('po_number');
            });
        }

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedInteger('credit_days')->nullable()->after('payment_mode');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_orders', 'credit_days')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropColumn('credit_days');
            });
        }

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('po_number')->nullable()->after('order_number');
        });
    }
};
