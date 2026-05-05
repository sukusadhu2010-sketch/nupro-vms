<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained()->onDelete('cascade');
            $table->string('quote_number')->unique();
            $table->integer('version')->default(1);
            $table->enum('status', ['draft', 'sent', 'accepted', 'expired', 'revised'])->default('draft');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();
            
            $table->index('enquiry_id');
            $table->index('quote_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};

