<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            $table->string('fy_label', 20)->unique();   // e.g. 2026-27
            $table->string('fy_short_code', 10)->unique(); // e.g. 26-27
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('quotation_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_year_id')->constrained('financial_years')->onDelete('cascade');
            $table->string('document_type', 10); // QTN / ENQ
            $table->unsignedBigInteger('last_serial')->default(0);
            $table->timestamps();
            $table->unique(['financial_year_id', 'document_type']);
        });

        // Tag documents with the FY they belong to
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('financial_year_id')->nullable()->after('enquiry_id')
                ->constrained('financial_years')->nullOnDelete();
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->foreignId('financial_year_id')->nullable()->after('customer_id')
                ->constrained('financial_years')->nullOnDelete();
            $table->string('enquiry_number')->nullable()->unique()->after('customer_id');
        });

        // Rich-text notes
        DB::statement('ALTER TABLE quotations MODIFY notes LONGTEXT NULL');
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn('enquiry_number');
            $table->dropConstrainedForeignId('financial_year_id');
        });
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('financial_year_id');
        });
        Schema::dropIfExists('quotation_counters');
        Schema::dropIfExists('financial_years');
    }
};
