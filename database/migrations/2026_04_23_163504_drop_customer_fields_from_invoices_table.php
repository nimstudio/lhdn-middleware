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
            $table->dropColumn([
                'customer_name',
                'customer_tin',
                'customer_registration_number',
                'customer_email',
                'customer_phone',
                'customer_address',
                'customer_street_address',
                'customer_city',
                'customer_state',
                'customer_postal_code',
                'customer_country',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('customer_name')->nullable();
            $table->string('customer_tin')->nullable();
            $table->string('customer_registration_number')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_street_address')->nullable();
            $table->string('customer_city')->nullable();
            $table->string('customer_state')->nullable();
            $table->string('customer_postal_code')->nullable();
            $table->string('customer_country')->nullable();
        });
    }
};
