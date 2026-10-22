<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bank Guarantee (SD/PBG) register.
     * Each record belongs to a sales order flagged sd_pbg = 1 and
     * optionally to an invoice raised against that order.
     */
    public function up(): void
    {
        // The invoices table exists in production but has no migration file.
        // Create it here when missing so fresh/test databases work.
        if (! Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->string('status')->default('issued');
                $table->date('invoice_date')->nullable();
                $table->date('due_date')->nullable();
                $table->decimal('subtotal', 14, 2)->default(0);
                $table->decimal('tax_amount', 14, 2)->default(0);
                $table->decimal('total_amount', 14, 2)->default(0);
                $table->decimal('paid_amount', 14, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->string('cancellation_reason')->nullable();
                $table->unsignedBigInteger('cancelled_by')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        Schema::create('bank_guarantees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->string('bg_number')->unique();
            $table->date('bg_date');
            $table->date('expiry_date');
            $table->date('claim_date')->nullable();
            $table->decimal('claim_amount', 14, 2)->default(0);
            $table->enum('status', ['active', 'expired', 'closed', 'released', 'invoked'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_guarantees');
    }
};
