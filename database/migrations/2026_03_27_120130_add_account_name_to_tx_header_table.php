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
        Schema::table('tx_header', function (Blueprint $table) {
            $table->string('account_name', 150)->nullable()->after('account_code');
            // Add index for search optimization
            $table->index('account_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_header', function (Blueprint $table) {
            $table->dropIndex(['account_name']);
            $table->dropColumn('account_name');
        });
    }
};
