<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiry_items', function (Blueprint $table) {
            if (!Schema::hasColumn('enquiry_items', 'unit_price')) {
                $table->decimal('unit_price', 12, 2)->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('enquiry_items', 'total_price')) {
                $table->decimal('total_price', 12, 2)->default(0)->after('unit_price');
            }
            foreach (['payment_methods' => 'text', 'moc' => 'string', 'mfg_spec' => 'string', 'trim' => 'string', 'operation' => 'string', 'end_connection' => 'string', 'rating' => 'string', 'media' => 'string', 'remarks' => 'text'] as $col => $type) {
                if (!Schema::hasColumn('enquiry_items', $col)) {
                    $table->{$type}($col)->nullable();
                }
            }
        });

        Schema::table('enquiry_items', function (Blueprint $table) {
            // Allow duplicate products per enquiry (repeater rows)
            // MySQL requires an index on the FK column before the unique index can be dropped
            if (!collect(DB::select('SHOW INDEX FROM enquiry_items'))->pluck('Key_name')->contains('enquiry_items_enquiry_id_index')) {
                $table->index('enquiry_id');
            }
            if (collect(DB::select('SHOW INDEX FROM enquiry_items'))->pluck('Key_name')->contains('enquiry_items_enquiry_id_product_id_unique')) {
                $table->dropUnique(['enquiry_id', 'product_id']);
            }
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            foreach (['payment_methods' => 'text', 'moc' => 'string', 'mfg_spec' => 'string', 'trim' => 'string', 'operation' => 'string', 'end_connection' => 'string', 'rating' => 'string', 'media' => 'string', 'remarks' => 'text'] as $col => $type) {
                if (!Schema::hasColumn('quotation_items', $col)) {
                    $table->{$type}($col)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('enquiry_items', function (Blueprint $table) {
            $table->unique(['enquiry_id', 'product_id']);
            $table->dropColumn(['unit_price', 'total_price', 'payment_methods', 'moc', 'mfg_spec', 'trim', 'operation', 'end_connection', 'rating', 'media', 'remarks']);
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn(['payment_methods', 'moc', 'mfg_spec', 'trim', 'operation', 'end_connection', 'rating', 'media', 'remarks']);
        });
    }
};
