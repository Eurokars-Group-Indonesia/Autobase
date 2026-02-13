<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ms_menus', function (Blueprint $table) {
            $table->string('menu_id', 50)->primary();
            $table->string('parent_id', 50)->nullable();
            $table->string('menu_code', 50);
            $table->string('menu_name', 100);
            $table->string('menu_url', 255)->nullable();
            $table->string('menu_icon', 50)->nullable();
            $table->unsignedInteger('menu_order')->default(0);
            $table->string('created_by', 50)->comment('User yang Create Data');
            $table->dateTime('created_date')->nullable()->useCurrent()->comment('Kapan data nya di Create');
            $table->string('updated_by', 50)->nullable()->comment('User yang Update Data');
            $table->dateTime('updated_date')->nullable()->comment('Kapan data nya di Update');
            $table->char('unique_id', 36)->unique()->comment('UUIDV4, di gunakan untuk Get Data dari URL');
            $table->enum('is_active', ['0', '1'])->default('1')->nullable()->comment('1 = Active, 0 = Inactive');

            // Indexes
            $table->index('menu_name');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('parent_id');
            $table->index('menu_order');
            $table->index('is_active');
        });

        Schema::create('ms_role_menus', function (Blueprint $table) {
            $table->string('role_menu_id', 50)->primary();
            $table->string('role_id', 50);
            $table->string('menu_id', 50);
            $table->string('created_by', 50)->comment('User yang Create Data');
            $table->dateTime('created_date')->nullable()->useCurrent()->comment('Kapan data nya di Create');
            $table->string('updated_by', 50)->nullable()->comment('User yang Update Data');
            $table->dateTime('updated_date')->nullable()->comment('Kapan data nya di Update');
            $table->char('unique_id', 36)->unique()->comment('UUIDV4, di gunakan untuk Get Data dari URL');
            $table->enum('is_active', ['0', '1'])->default('1')->nullable()->comment('1 = Active, 0 = Inactive');

            // Indexes
            $table->index('menu_id');
            $table->index('role_id');
            $table->index('is_active');
            
            // Foreign keys
            $table->foreign('role_id')->references('role_id')->on('ms_role')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('menu_id')->references('menu_id')->on('ms_menus')
                ->onUpdate('restrict')->onDelete('restrict');
        });
        
        // Add foreign keys to ms_menus
        Schema::table('ms_menus', function (Blueprint $table) {
            $table->foreign('created_by')->references('user_id')->on('ms_users')
                ->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('updated_by')->references('user_id')->on('ms_users')
                ->onUpdate('restrict')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_role_menus');
        Schema::dropIfExists('ms_menus');
    }
};
