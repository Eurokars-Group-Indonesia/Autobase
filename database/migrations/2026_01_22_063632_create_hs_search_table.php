<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hs_search', function (Blueprint $table) {
            $table->bigIncrements('search_id');
            $table->string('user_id', 50);
            $table->string('search', 255)->nullable()->comment('Search input query');
            $table->date('date_from')->nullable()->comment('Date from filter');
            $table->date('date_to')->nullable()->comment('Date to filter');
            $table->dateTime('executed_date')->useCurrent();
            $table->decimal('execution_time', 10, 2)->comment('Execution time in milliseconds');
            $table->enum('transaction_type', ['H', 'B'])->comment('H = Header, B = Body');
            
            // Indexes
            $table->index('user_id');
            $table->index('executed_date');
            $table->index('transaction_type');
            
            // Foreign key
            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('ms_users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hs_search');
    }
};
