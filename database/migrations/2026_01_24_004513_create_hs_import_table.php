<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hs_import', function (Blueprint $table) {
            $table->bigIncrements('import_id');
            $table->string('user_id', 50)->comment('User yang melakukan import');
            $table->unsignedInteger('total_row')->comment('Total Row pada Excel Import');
            $table->unsignedInteger('success_row')->comment('Total Row yang sukses Import');
            $table->unsignedInteger('error_row')->comment('Total Row yang gagal Import');
            $table->decimal('success_rate', 5, 2)->unsigned()->comment('Success rate');
            $table->dateTime('executed_date')->useCurrent();
            $table->decimal('execution_time', 10, 2)->comment('Total waktu eksekusi dalam milliseconds');
            
            // Indexes
            $table->index('user_id');
            $table->index('executed_date');
            
            // Foreign key
            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('ms_users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hs_import');
    }
};
