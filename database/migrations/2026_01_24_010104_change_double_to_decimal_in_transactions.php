<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change tx_body columns from double to decimal
        Schema::table('tx_body', function (Blueprint $table) {
            $table->decimal('qty', 10, 2)->default(0)->change();
            $table->decimal('selling_price', 20, 2)->change();
            $table->decimal('discount', 5, 2)->default(0)->change();
            $table->decimal('extended_price', 20, 2)->default(0)->change();
            $table->decimal('menu_price', 20, 2)->default(0)->change();
            $table->decimal('cost_price', 20, 2)->default(0)->change();
            $table->decimal('contribution', 5, 2)->default(0)->change();
            $table->decimal('currency_price', 20, 2)->nullable()->change();
        });

        // Change tx_header columns from double to decimal
        Schema::table('tx_header', function (Blueprint $table) {
            $table->decimal('exchange_rate', 10, 2)->nullable()->change();
            $table->decimal('gross_value', 20, 2)->nullable()->change();
            $table->decimal('net_value', 20, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Revert tx_body columns back to double
        Schema::table('tx_body', function (Blueprint $table) {
            $table->double('qty', 10, 2)->default(0)->change();
            $table->double('selling_price', 20, 2)->change();
            $table->double('discount', 5, 2)->default(0)->change();
            $table->double('extended_price', 20, 2)->default(0)->change();
            $table->double('menu_price', 20, 2)->default(0)->change();
            $table->double('cost_price', 20, 2)->default(0)->change();
            $table->double('contribution', 5, 2)->default(0)->change();
            $table->double('currency_price', 20, 2)->nullable()->change();
        });

        // Revert tx_header columns back to double
        Schema::table('tx_header', function (Blueprint $table) {
            $table->double('exchange_rate', 10, 2)->nullable()->change();
            $table->double('gross_value', 20, 2)->nullable()->change();
            $table->double('net_value', 20, 2)->nullable()->change();
        });
    }
};
