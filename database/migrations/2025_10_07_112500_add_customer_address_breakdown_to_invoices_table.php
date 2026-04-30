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
            // Add breakdown address fields after customer_address
            $table->string('customer_street_address')->nullable()->after('customer_address');
            $table->string('customer_city')->nullable()->after('customer_street_address');
            $table->string('customer_state')->nullable()->after('customer_city');
            $table->string('customer_postal_code', 10)->nullable()->after('customer_state');
            $table->string('customer_country', 3)->default('MYS')->after('customer_postal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'customer_street_address',
                'customer_city',
                'customer_state',
                'customer_postal_code',
                'customer_country',
            ]);
        });
    }
};
