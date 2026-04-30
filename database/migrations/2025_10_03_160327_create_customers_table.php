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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Address fields
            $table->string('street_address')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('state_id')->nullable()->constrained()->onDelete('set null');
            $table->string('postal_code', 10)->nullable();
            $table->string('country', 3)->default('MYS'); // ISO 3166-1 alpha-3 country code

            // eInvoice fields (optional)
            $table->string('tin', 50)->nullable()->comment('Tax Identification Number');
            $table->enum('document_type', ['BRN', 'NRIC', 'PASSPORT', 'ARMY'])->nullable();
            $table->string('document_number', 50)->nullable();

            // Metadata
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
