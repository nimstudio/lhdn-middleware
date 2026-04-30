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
        // Add api_key columns to companies table
        Schema::table('companies', function (Blueprint $table) {
            $table->string('api_key')->nullable()->unique()->after('business_type_id');
            $table->timestamp('api_key_created_at')->nullable()->after('api_key');
        });

        // Remove from users table if exists
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['api_key', 'api_key_created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['api_key', 'api_key_created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('api_key')->nullable()->unique()->after('password');
            $table->timestamp('api_key_created_at')->nullable()->after('api_key');
        });
    }
};
