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
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_mode', ['cash', 'fund_transfer', 'cheque', 'upi'])->default('cash');
            $table->string('upi_id')->nullable();
            $table->string('cheque_number')->nullable();
            $table->text('bank_details')->nullable();
            $table->text('beneficiary_details')->nullable();
            $table->string('transaction_id')->nullable();
            $table->date('payment_date')->default(now()->toDateString());
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'payment_date']);
            $table->index('payment_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};

