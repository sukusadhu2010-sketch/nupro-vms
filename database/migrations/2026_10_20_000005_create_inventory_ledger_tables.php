<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Raw movement log — append-only, never edited.
        if (! Schema::hasTable('inventory_ledgers')) {
        Schema::create('inventory_ledgers', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->enum('transaction_type', ['po', 'grn', 'issue', 'transfer', 'adjustment'])->index();

            $table->string('reference_no')->nullable();     // GRN/2026-27/0001 etc.
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // Warehouse master not yet implemented — plain nullable column, no FK.
            $table->unsignedBigInteger('warehouse_id')->nullable()->index();

            $table->decimal('qty_in', 12, 3)->default(0);
            $table->decimal('qty_out', 12, 3)->default(0);
            $table->decimal('balance_qty', 12, 3)->default(0);
            $table->string('unit', 20)->nullable();
            $table->text('remarks')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'transaction_date']);
            $table->index(['purchase_order_id', 'product_id']);
        });
        }

        // Per product/warehouse current balance, kept in sync with the ledger.
        if (! Schema::hasTable('stock_balances')) {
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('warehouse_id')->nullable()->index();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
        Schema::dropIfExists('inventory_ledgers');
    }
};
