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
        Schema::create('ms_user_brand', function (Blueprint $table) {
            $table->string('user_brand_id', 50)->primary();
            $table->string('user_id', 50);
            $table->string('brand_id', 50);
            $table->string('created_by', 50)->comment('User yang Create Data');
            $table->dateTime('created_date')->nullable()->useCurrent()->comment('Kapan data nya di Create');
            $table->string('updated_by', 50)->nullable()->comment('User yang Update Data');
            $table->dateTime('updated_date')->nullable()->comment('Kapan data nya di Update');
            $table->char('unique_id', 36)->unique()->comment('UUIDV4, di gunakan untuk Get Data dari URL');
            $table->enum('is_active', ['0', '1'])->default('1')->comment('1 = Active, 0 = Inactive');

            // Indexes
            $table->index('user_id');
            $table->index('brand_id');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('is_active');

            // Foreign keys
            $table->foreign('user_id')->references('user_id')->on('ms_users')->onDelete('cascade');
            $table->foreign('brand_id')->references('brand_id')->on('ms_brand')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_user_brand');
    }
};
