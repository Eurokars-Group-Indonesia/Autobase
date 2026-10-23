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
            // Change phone_number fields from 20 to 50 characters
            // This allows storing phone numbers with names like "8954324234 Bernando Torrez"
            $table->string('phone_number_1', 50)->nullable()->change();
            $table->string('phone_number_2', 50)->nullable()->change();
            $table->string('phone_number_3', 50)->nullable()->change();
            $table->string('phone_number_4', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_header', function (Blueprint $table) {
            // Revert phone_number fields back to 20 characters
            $table->string('phone_number_1', 20)->nullable()->change();
            $table->string('phone_number_2', 20)->nullable()->change();
            $table->string('phone_number_3', 20)->nullable()->change();
            $table->string('phone_number_4', 20)->nullable()->change();
        });
    }
};
