<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ms_dealers', function (Blueprint $table) {
            $table->string('dealer_id', 50)->primary();
            $table->string('dealer_code', 50);
            $table->string('dealer_name', 150);
            $table->string('city', 100)->nullable();
            $table->string('created_by', 50)->comment('User yang Create Data');
            $table->dateTime('created_date')->nullable()->useCurrent()->comment('Kapan data nya di Create');
            $table->string('updated_by', 50)->nullable()->comment('User yang Update Data');
            $table->dateTime('updated_date')->nullable()->comment('Kapan data nya di Update');
            $table->char('unique_id', 36)->unique()->comment('UUIDV4, di gunakan untuk Get Data dari URL');
            $table->enum('is_active', ['0', '1'])->nullable()->default('1')->comment('1 = Active, 0 = Inactive');
            
            // Indexes
            $table->index('dealer_name');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('is_active');
            
            // Foreign keys
            $table->foreign('created_by')->references('user_id')->on('ms_users')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('updated_by')->references('user_id')->on('ms_users')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_dealers');
    }
};
