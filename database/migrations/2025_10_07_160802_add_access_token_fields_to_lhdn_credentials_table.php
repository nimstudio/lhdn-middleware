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
        Schema::table('lhdn_credentials', function (Blueprint $table) {
            $table->text('access_token')->nullable()->after('mode');
            $table->string('token_type')->nullable()->after('access_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lhdn_credentials', function (Blueprint $table) {
            $table->dropColumn('access_token');
            $table->dropColumn('token_type');
        });
    }
};
