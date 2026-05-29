<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft', 'sent', 'accepted', 'expired', 'revised', 'converted') DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft', 'sent', 'accepted', 'expired', 'revised') DEFAULT 'draft'");
    }
};

