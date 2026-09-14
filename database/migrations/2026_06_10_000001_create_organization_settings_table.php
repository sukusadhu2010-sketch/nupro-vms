<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('abbreviation', 10); // used in document numbers, e.g. NES
            $table->string('logo_path')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 20)->nullable();
            $table->string('country')->nullable()->default('India');
            $table->string('gstin', 30)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('bank_details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
    }
};
