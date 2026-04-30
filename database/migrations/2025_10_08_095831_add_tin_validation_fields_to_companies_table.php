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
        Schema::table('companies', function (Blueprint $table) {
            $table->timestamp('tin_verified_at')->nullable()->after('tin_number');
            $table->enum('tin_status', ['pending', 'valid', 'invalid'])->default('pending')->after('tin_verified_at');
            $table->timestamp('last_tin_check_at')->nullable()->after('tin_status');
            $table->enum('tin_source', ['user', 'sdk'])->nullable()->after('last_tin_check_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['tin_verified_at', 'tin_status', 'last_tin_check_at', 'tin_source']);
        });
    }
};
