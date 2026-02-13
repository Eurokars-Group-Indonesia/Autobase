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
        Schema::table('hs_import', function (Blueprint $table) {
            $table->enum('transaction_type', ['H', 'B'])->default('H')->after('user_id')->comment('H = Header, B = Body');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hs_import', function (Blueprint $table) {
            $table->dropColumn('transaction_type');
        });
    }
};
