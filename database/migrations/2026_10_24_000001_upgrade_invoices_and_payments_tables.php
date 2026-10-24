<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'sales_order_id')) {
                $table->unsignedBigInteger('sales_order_id')->nullable()->change();
            }
            if (Schema::hasColumn('invoices', 'status')) {
                $table->string('status', 30)->default('draft')->change();
            }
            if (!Schema::hasColumn('invoices', 'type')) {
                $table->string('type', 30)->default('tax')->after('invoice_number');
            }
            if (!Schema::hasColumn('invoices', 'purchase_order_id')) {
                $table->unsignedBigInteger('purchase_order_id')->nullable()->after('sales_order_id');
            }
            if (!Schema::hasColumn('invoices', 'delivery_id')) {
                $table->unsignedBigInteger('delivery_id')->nullable()->after('purchase_order_id');
            }
            if (!Schema::hasColumn('invoices', 'vendor_id')) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('invoices', 'po_number')) {
                $table->string('po_number')->nullable()->after('invoice_date');
            }
            if (!Schema::hasColumn('invoices', 'po_date')) {
                $table->date('po_date')->nullable()->after('po_number');
            }
            if (!Schema::hasColumn('invoices', 'customer_gst')) {
                $table->string('customer_gst', 50)->nullable()->after('po_date');
            }
            if (!Schema::hasColumn('invoices', 'billing_address')) {
                $table->text('billing_address')->nullable()->after('customer_gst');
            }
            if (!Schema::hasColumn('invoices', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('billing_address');
            }
            if (!Schema::hasColumn('invoices', 'sub_total')) {
                $table->decimal('sub_total', 12, 2)->default(0)->after('shipping_address');
            }
            if (!Schema::hasColumn('invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('sub_total');
            }
            if (!Schema::hasColumn('invoices', 'taxable_amount')) {
                $table->decimal('taxable_amount', 12, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('invoices', 'cgst')) {
                $table->decimal('cgst', 12, 2)->default(0)->after('taxable_amount');
            }
            if (!Schema::hasColumn('invoices', 'sgst')) {
                $table->decimal('sgst', 12, 2)->default(0)->after('cgst');
            }
            if (!Schema::hasColumn('invoices', 'igst')) {
                $table->decimal('igst', 12, 2)->default(0)->after('sgst');
            }
            if (!Schema::hasColumn('invoices', 'cess')) {
                $table->decimal('cess', 12, 2)->default(0)->after('igst');
            }
            if (!Schema::hasColumn('invoices', 'tcs')) {
                $table->decimal('tcs', 12, 2)->default(0)->after('cess');
            }
            if (!Schema::hasColumn('invoices', 'shipping_charge')) {
                $table->decimal('shipping_charge', 12, 2)->default(0)->after('tcs');
            }
            if (!Schema::hasColumn('invoices', 'round_off')) {
                $table->decimal('round_off', 12, 2)->default(0)->after('shipping_charge');
            }
            if (!Schema::hasColumn('invoices', 'grand_total')) {
                $table->decimal('grand_total', 12, 2)->default(0)->after('round_off');
            }
            if (!Schema::hasColumn('invoices', 'terms')) {
                $table->text('terms')->nullable()->after('status');
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_items', 'sales_order_item_id')) {
                $table->unsignedBigInteger('sales_order_item_id')->nullable()->change();
            }
            if (Schema::hasColumn('invoice_items', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->change();
            }
            if (Schema::hasColumn('invoice_items', 'description')) {
                $table->text('description')->nullable()->change();
            }
            if (Schema::hasColumn('invoice_items', 'quantity')) {
                $table->decimal('quantity', 12, 3)->default(1)->change();
            }
            if (!Schema::hasColumn('invoice_items', 'invoiceable_id')) {
                $table->unsignedBigInteger('invoiceable_id')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('invoice_items', 'invoiceable_type')) {
                $table->string('invoiceable_type')->nullable()->after('invoiceable_id');
            }
            if (!Schema::hasColumn('invoice_items', 'item_name')) {
                $table->string('item_name')->nullable()->after('invoiceable_type');
            }
            if (!Schema::hasColumn('invoice_items', 'hsn_code')) {
                $table->string('hsn_code', 50)->nullable()->after('item_name');
            }
            if (!Schema::hasColumn('invoice_items', 'unit')) {
                $table->string('unit', 20)->default('Nos')->after('quantity');
            }
            if (!Schema::hasColumn('invoice_items', 'rate')) {
                $table->decimal('rate', 12, 2)->default(0)->after('unit');
            }
            if (!Schema::hasColumn('invoice_items', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0)->after('rate');
            }
            if (!Schema::hasColumn('invoice_items', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_percent');
            }
            if (!Schema::hasColumn('invoice_items', 'tax_percent')) {
                $table->decimal('tax_percent', 5, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('invoice_items', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percent');
            }
            if (!Schema::hasColumn('invoice_items', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0)->after('tax_amount');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'payable_id')) {
                $table->unsignedBigInteger('payable_id')->nullable()->change();
            }
            if (Schema::hasColumn('payments', 'payable_type')) {
                $table->string('payable_type')->nullable()->change();
            }
            if (Schema::hasColumn('payments', 'payment_mode')) {
                $table->string('payment_mode', 50)->default('bank_transfer')->change();
            }
            if (!Schema::hasColumn('payments', 'payment_number')) {
                $table->string('payment_number')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('payments', 'direction')) {
                $table->string('direction', 20)->default('receipt')->after('payment_number');
            }
            if (!Schema::hasColumn('payments', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('direction');
            }
            if (!Schema::hasColumn('payments', 'vendor_id')) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('payments', 'purchase_order_id')) {
                $table->unsignedBigInteger('purchase_order_id')->nullable()->after('vendor_id');
            }
            if (!Schema::hasColumn('payments', 'invoice_id')) {
                $table->unsignedBigInteger('invoice_id')->nullable()->after('purchase_order_id');
            }
            if (!Schema::hasColumn('payments', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('payment_mode');
            }
            if (!Schema::hasColumn('payments', 'remarks')) {
                $table->text('remarks')->nullable()->after('reference_number');
            }
            if (!Schema::hasColumn('payments', 'status')) {
                $table->string('status', 30)->default('completed')->after('remarks');
            }
        });
    }

    public function down(): void
    {
        // Safe reversible migration if needed
    }
};

