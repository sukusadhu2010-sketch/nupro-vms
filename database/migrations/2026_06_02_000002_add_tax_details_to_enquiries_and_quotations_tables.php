<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['enquiries', 'quotations'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                if (!Schema::hasColumn($t->getTable(), 'tax_type')) {
                    $t->string('tax_type')->default('igst')->after('total_amount'); // igst | sgst_cgst
                }
                if (!Schema::hasColumn($t->getTable(), 'igst')) {
                    $t->decimal('igst', 12, 2)->default(0)->after('tax_type');
                }
                if (!Schema::hasColumn($t->getTable(), 'sgst')) {
                    $t->decimal('sgst', 12, 2)->default(0)->after('igst');
                }
                if (!Schema::hasColumn($t->getTable(), 'cgst')) {
                    $t->decimal('cgst', 12, 2)->default(0)->after('sgst');
                }
                if (!Schema::hasColumn($t->getTable(), 'tax_amount')) {
                    $t->decimal('tax_amount', 12, 2)->default(0)->after('cgst');
                }
                if (!Schema::hasColumn($t->getTable(), 'amount_in_words')) {
                    $t->text('amount_in_words')->nullable()->after('tax_amount');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['enquiries', 'quotations'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['tax_type', 'igst', 'sgst', 'cgst', 'tax_amount', 'amount_in_words']);
            });
        }
    }
};
