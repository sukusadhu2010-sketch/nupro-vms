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
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('account_name')->unique(); // cash, bank_shyamsteel, post_office_main
            $table->enum('account_type', ['cash', 'bank', 'post_office']);
            $table->decimal('balance', 12, 2)->default(0);
            $table->timestamp('last_updated')->useCurrent();
            $table->text('notes')->nullable();
            
            $table->index('account_type');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
