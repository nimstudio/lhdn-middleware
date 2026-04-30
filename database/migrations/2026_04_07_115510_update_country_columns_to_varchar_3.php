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
        // Update customers table country column to varchar(3) for MYS code
        Schema::table('customers', function (Blueprint $table) {
            $table->string('country', 3)->default('MYS')->change();
        });

        // Update invoices table customer_country column to varchar(3) for MYS code
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('customer_country', 3)->default('MYS')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert customers table country column back to varchar(2)
        Schema::table('customers', function (Blueprint $table) {
            $table->string('country', 2)->default('MY')->change();
        });

        // Revert invoices table customer_country column back to varchar(2)
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('customer_country', 2)->default('MY')->change();
        });
    }
};
