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
        if (!Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->index(['status', 'name']);
            });
        }

        // Link products to categories
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'product_category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('product_category_id')->nullable()->after('unit_id')->constrained('product_categories')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'product_category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_category_id');
            });
        }

        Schema::dropIfExists('product_categories');
    }
};
