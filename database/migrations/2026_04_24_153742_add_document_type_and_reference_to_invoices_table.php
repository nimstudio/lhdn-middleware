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
            $table->enum('document_type', ['invoice', 'credit_note', 'debit_note'])->default('invoice')->after('uuid');
            $table->foreignId('original_invoice_id')->nullable()->after('document_type')->constrained('invoices')->onDelete('set null');
            $table->index(['document_type', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['original_invoice_id']);
            $table->dropColumn(['document_type', 'original_invoice_id']);
        });
    }
};
