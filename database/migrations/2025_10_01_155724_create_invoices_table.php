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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('invoice_number', 50);
            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            // Customer information
            $table->string('customer_name');
            $table->string('customer_tin')->nullable();
            $table->string('customer_registration_number')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();

            // Amounts
            $table->string('currency', 3)->default('MYR');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->text('notes')->nullable();

            // LHDN submission tracking
            $table->enum('lhdn_status', ['draft', 'pending', 'submitted', 'accepted', 'rejected'])->default('draft');
            $table->string('lhdn_submission_id')->nullable()->comment('LHDN reference');
            $table->timestamp('lhdn_submitted_at')->nullable();
            $table->json('lhdn_response')->nullable()->comment('Full API response');
            $table->text('lhdn_error_message')->nullable();

            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'created_at']);
            $table->index(['company_id', 'lhdn_status']);
            $table->index('invoice_number');
            $table->index('invoice_date');
            $table->index('lhdn_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
