<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Both customer receipts and vendor payments share one table with a direction flag.
        // Guard against tables created by an earlier/seeded environment.
        if (! Schema::hasTable('payments')) {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();      // RCPT/2026-27/00001 or PYMT/2026-27/00001
            $table->enum('direction', ['receipt', 'payment'])->index(); // receipt = from customer, payment = to vendor

            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();

            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'cheque', 'neft', 'rtgs', 'upi'])
                ->default('bank_transfer');
            $table->string('reference_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->date('cheque_date')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['reference_number', 'direction']);
        });
        }

        if (! Schema::hasTable('payment_allocations')) {
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('allocated_amount', 12, 2);
            $table->timestamps();

            $table->unique(['payment_id', 'invoice_id']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
    }
};
