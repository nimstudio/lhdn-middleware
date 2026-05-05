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
            if (!Schema::hasColumn('invoices', 'document_type')) {
                $table->string('document_type', 10)->default('01')->after('uuid');
            }
            if (!Schema::hasColumn('invoices', 'original_invoice_id')) {
                $table->foreignId('original_invoice_id')->nullable()->after('document_type')->constrained('invoices')->onDelete('set null');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'original_invoice_id')) {
                $table->dropForeign(['original_invoice_id']);
                $table->dropColumn('original_invoice_id');
            }
            if (Schema::hasColumn('invoices', 'document_type')) {
                $table->dropColumn('document_type');
            }
        });
    }
};
