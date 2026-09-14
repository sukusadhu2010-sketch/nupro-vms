<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_forms', function (Blueprint $table) {
            $table->id();

            // Header
            $table->string('company_name')->default('Engineering Syndicate (P) Ltd.');
            $table->string('certification')->nullable()->default('An ISO 9001:2015 Certified Company');
            $table->string('ref_no')->unique();                    // NES/YY-YY/QTN-XXX
            $table->date('form_date');
            $table->string('title')->default('QUOTATION / OFFER');

            // Recipient
            $table->string('to_company');
            $table->text('to_address')->nullable();
            $table->text('project_details')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_designation')->nullable();

            // Body
            $table->text('acknowledgement')->nullable();
            $table->string('product_spec');                        // e.g. SLUICE GATE / OPEN CHANNEL GATE
            $table->json('description_entries')->nullable();       // [{moc, mfg_spec, trim, operation, end_connection, rating, media}]

            // Item table
            $table->json('items')->nullable();                     // [{size_mm, quantity, unit_price, total_price}]
            $table->decimal('grand_total', 14, 2)->default(0);

            // Terms & Conditions
            $table->string('delivery_terms')->nullable();          // F.O.R. site / Ex Works
            $table->decimal('igst_percent', 5, 2)->default(0);
            $table->decimal('sgst_percent', 5, 2)->default(0);
            $table->decimal('cgst_percent', 5, 2)->default(0);
            $table->text('payment_terms')->nullable();
            $table->text('inspection_vendor_scope')->nullable();
            $table->text('inspection_third_party_scope')->nullable();
            $table->string('warranty_terms')->nullable();          // 18 months from supply / 12 months from commissioning

            // Footer
            $table->text('notes')->nullable();
            $table->string('closing_statement')->default('Thanking You');
            $table->string('signatory_company')->nullable();
            $table->string('signatory_designation')->nullable();

            $table->foreignId('enquiry_id')->nullable()->constrained('enquiries')->nullOnDelete();
            $table->string('status')->default('draft');            // draft / final
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_forms');
    }
};
