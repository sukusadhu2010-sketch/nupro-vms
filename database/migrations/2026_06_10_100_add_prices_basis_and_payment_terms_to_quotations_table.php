<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'prices_basis')) {
                $table->text('prices_basis')->nullable()->after('payment_terms');
            }
            if (!Schema::hasColumn('quotations', 'prices_basis_option')) {
                $table->string('prices_basis_option')->nullable()->after('prices_basis');
            }
            if (!Schema::hasColumn('quotations', 'payment_term_option')) {
                $table->string('payment_term_option')->nullable()->after('prices_basis_option');
            }
            if (!Schema::hasColumn('quotations', 'payment_term_text')) {
                $table->json('payment_term_text')->nullable()->after('payment_term_option');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            foreach (['payment_term_text', 'payment_term_option', 'prices_basis_option', 'prices_basis'] as $col) {
                if (Schema::hasColumn('quotations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
