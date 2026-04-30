<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include new validation-based status values
        DB::statement("ALTER TABLE invoices MODIFY COLUMN lhdn_status ENUM('draft', 'pending', 'submitted', 'accepted', 'rejected', 'valid', 'invalid', 'cancelled') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE invoices MODIFY COLUMN lhdn_status ENUM('draft', 'pending', 'submitted', 'accepted', 'rejected') DEFAULT 'draft'");
    }
};
