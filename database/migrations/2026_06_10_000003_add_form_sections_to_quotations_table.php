<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Acknowledgement & Product Specification
            $table->text('acknowledgement')->nullable();
            $table->string('product_spec')->nullable();

            // Terms & Conditions
            $table->string('delivery_terms')->nullable();
            $table->string('warranty_terms')->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('inspection_vendor_scope')->nullable();
            $table->text('inspection_third_party_scope')->nullable();

            // Notes & Signatory
            $table->string('closing_statement')->nullable()->default('Thanking You');
            $table->string('signatory_company')->nullable();
            $table->string('signatory_designation')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'acknowledgement', 'product_spec',
                'delivery_terms', 'warranty_terms', 'payment_terms',
                'inspection_vendor_scope', 'inspection_third_party_scope',
                'closing_statement', 'signatory_company', 'signatory_designation',
            ]);
        });
    }
};
