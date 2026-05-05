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
        Schema::table('invoices', function (Blueprint $table) {
            // Update the enum values for document_type
            $table->enum('document_type', ['01', '02', '03', '04', '11', '12', '13', '14'])->default('01')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Revert to old enum values
            $table->enum('document_type', ['invoice', 'credit_note', 'debit_note'])->default('invoice')->change();
        });
    }


};
