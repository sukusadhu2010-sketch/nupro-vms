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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payable_id');
            $table->string('payable_type');
            $table->enum('transaction_type', ['income', 'expense', 'contra']);
            //$table->foreignId('contact_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('contact_type', ['customer', 'vendor'])->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_mode', ['cash', 'fund_transfer', 'cheque', 'upi']);
            $table->date('payment_date');
            $table->string('upi_id', 100)->nullable();

            $table->string('cheque_number', 50)->nullable();

            $table->date('cheque_date')->nullable();
            $table->string('bank_name', 100)->nullable();

            $table->text('beneficiary_details')->nullable();
            $table->string('transaction_id', 100)->nullable();

            //$table->foreignId('ledger_account_id')->constrained('ledgers')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            
            $table->index(['payable_id', 'payable_type']);
            $table->index('transaction_type');
            $table->index('payment_date');
          //  $table->index('ledger_account_id');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
