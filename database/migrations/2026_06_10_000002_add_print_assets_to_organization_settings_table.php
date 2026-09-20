<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->string('seal_path')->nullable()->after('logo_path');
            $table->string('signature_path')->nullable()->after('seal_path');
            $table->string('letterhead_path')->nullable()->after('signature_path');
        });
    }

    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->dropColumn(['seal_path', 'signature_path', 'letterhead_path']);
        });
    }
};
