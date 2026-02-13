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
        Schema::create('ad_trail_log_master_data', function (Blueprint $table) {
            $table->id('audit_trail_log_master_data_id');
            $table->string('user_id', 50);
            $table->string('screen_id', 50);
            $table->longText('old_response')->nullable()->comment('Perubahan Sebelum');
            $table->longText('new_response')->nullable()->comment('Perubahan Setelah');
            $table->enum('execution_type', ['INSERT', 'UPDATE', 'DELETE'])->nullable();
            $table->dateTime('executed_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trail_log_master_data');
    }
};
