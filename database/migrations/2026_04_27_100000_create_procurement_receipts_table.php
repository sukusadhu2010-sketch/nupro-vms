<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_id')->constrained()->onDelete('cascade');
            $table->foreignId('procurement_item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_received');
            $table->timestamp('received_at');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('procurement_id');
            $table->index('procurement_item_id');
            $table->index('user_id');
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_receipts');
    }
};

