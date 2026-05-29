<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->onDelete('restrict');
            $table->foreignId('vendor_id')->constrained()->onDelete('restrict');
            $table->string('po_number')->unique();
            $table->enum('status', ['draft', 'sent', 'partially_received', 'received', 'cancelled'])->default('draft');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->date('order_date')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sales_order_id');
            $table->index('vendor_id');
            $table->index('po_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};

