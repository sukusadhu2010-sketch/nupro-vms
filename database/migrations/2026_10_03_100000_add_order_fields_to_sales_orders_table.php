<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('customer_po_number')->nullable()->after('po_number');
            $table->date('customer_po_date')->nullable()->after('order_number');
            $table->boolean('mtc')->default(false)->after('customer_po_date');
            $table->boolean('pdi')->default(false)->after('mtc');
            $table->date('delivery_target_date')->nullable()->after('pdi');
            $table->string('payment_mode', 20)->nullable()->after('delivery_target_date');
        });

        // Enforce uniqueness on job_number (existing rows are already handled
        // by the earlier migration / service; safe for nulls).
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unique('job_number', 'sales_orders_job_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropUnique('sales_orders_job_number_unique');
            $table->dropColumn([
                'customer_po_number',
                'customer_po_date',
                'mtc',
                'pdi',
                'delivery_target_date',
                'payment_mode',
            ]);
        });
    }
};
