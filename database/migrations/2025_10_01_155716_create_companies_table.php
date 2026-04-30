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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('registration_number')->unique()->comment('SSM number');
            $table->string('tin_number')->unique()->comment('Tax Identification Number');
            $table->string('email');
            $table->string('phone');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->foreignId('state_id')->constrained()->cascadeOnDelete();
            $table->string('postcode');
            $table->string('country')->default('Malaysia');
            $table->enum('status', ['pending', 'active', 'suspended', 'cancelled'])->default('pending');
            $table->boolean('onboarding_completed')->default(false);

            // Subscription fields
            $table->foreignId('subscription_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('subscription_status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->date('subscription_starts_at')->nullable();
            $table->date('subscription_ends_at')->nullable();
            $table->string('subscription_payment_proof')->nullable();
            $table->foreignId('subscription_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('subscription_approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('subscription_status');
            $table->index(['subscription_status', 'subscription_ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
