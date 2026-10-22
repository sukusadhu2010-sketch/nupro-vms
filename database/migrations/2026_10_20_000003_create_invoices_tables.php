<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Some environments already created the invoices tables earlier
        // (e.g. an older migration or seed). Only create when missing.
        if (! Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();      // INV/2026-27/00001
            $table->enum('type', ['tax', 'proforma', 'commercial'])->default('tax');

            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('delivery_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();

            $table->date('invoice_date');
            $table->string('po_number')->nullable();
            $table->date('po_date')->nullable();

            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('customer_gst')->nullable();

            $table->decimal('sub_total', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('cgst', 12, 2)->default(0);
            $table->decimal('sgst', 12, 2)->default(0);
            $table->decimal('igst', 12, 2)->default(0);
            $table->decimal('cess', 12, 2)->default(0);
            $table->decimal('tcs', 12, 2)->default(0);
            $table->decimal('shipping_charge', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('round_off', 12, 2)->default(0);

            $table->enum('status', ['draft', 'pending', 'approved', 'cancelled', 'partially_paid', 'paid'])
                ->default('draft');
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'invoice_date']);
            $table->index('customer_id');
            });
        }

        if (! Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoiceable_id')->nullable();
            $table->string('invoiceable_type')->nullable(); // SO item / PO item / delivery line
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->string('hsn_code')->nullable();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->string('unit', 20)->default('Nos');
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
