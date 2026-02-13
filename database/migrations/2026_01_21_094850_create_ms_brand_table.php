<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ms_brand', function (Blueprint $table) {
            $table->string('brand_id', 50)->primary();
            $table->string('brand_code', 50);
            $table->string('brand_name', 100);
            $table->string('brand_group', 100)->nullable();
            $table->string('country_origin', 100)->nullable();
            $table->string('created_by', 50)->comment('User yang Create Data');
            $table->dateTime('created_date')->nullable()->useCurrent()->comment('Kapan data nya di Create');
            $table->string('updated_by', 50)->nullable()->comment('User yang Update Data');
            $table->dateTime('updated_date')->nullable()->comment('Kapan data nya di Update');
            $table->char('unique_id', 36)->unique()->comment('UUIDV4, di gunakan untuk Get Data dari URL');
            $table->enum('is_active', ['0', '1'])->nullable()->default('1')->comment('1 = Active, 0 = Inactive');
            
            // Indexes
            $table->index('brand_name');
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
        Schema::dropIfExists('ms_brand');
    }
};
