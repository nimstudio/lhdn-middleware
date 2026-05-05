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
        // Update existing document_type values to new codes
        \DB::table('invoices')->where('document_type', 'invoice')->update(['document_type' => '01']);
        \DB::table('invoices')->where('document_type', 'credit_note')->update(['document_type' => '02']);
        \DB::table('invoices')->where('document_type', 'debit_note')->update(['document_type' => '03']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert document_type values
        \DB::table('invoices')->where('document_type', '01')->update(['document_type' => 'invoice']);
        \DB::table('invoices')->where('document_type', '02')->update(['document_type' => 'credit_note']);
        \DB::table('invoices')->where('document_type', '03')->update(['document_type' => 'debit_note']);
    }


};
