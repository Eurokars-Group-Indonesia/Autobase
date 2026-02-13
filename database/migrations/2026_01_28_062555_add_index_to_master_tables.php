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
        // Add index to ms_brand.brand_code
        Schema::table('ms_brand', function (Blueprint $table) {
            $table->index('brand_code', 'idx_brand_code');
        });

        // Add index to ms_dealers.dealer_code
        Schema::table('ms_dealers', function (Blueprint $table) {
            $table->index('dealer_code', 'idx_dealer_code');
        });

        // Add index to ms_menus.menu_code
        Schema::table('ms_menus', function (Blueprint $table) {
            $table->index('menu_code', 'idx_menu_code');
        });

        // Add index to ms_permissions.permission_code
        Schema::table('ms_permissions', function (Blueprint $table) {
            $table->index('permission_code', 'idx_permission_code');
        });

        // Add index to ms_role.role_code
        Schema::table('ms_role', function (Blueprint $table) {
            $table->index('role_code', 'idx_role_code');
        });

        // Add composite index to ms_role_menus (role_id, menu_id)
        Schema::table('ms_role_menus', function (Blueprint $table) {
            $table->index(['role_id', 'menu_id'], 'idx_role_menu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop index from ms_brand.brand_code
        Schema::table('ms_brand', function (Blueprint $table) {
            $table->dropIndex('idx_brand_code');
        });

        // Drop index from ms_dealers.dealer_code
        Schema::table('ms_dealers', function (Blueprint $table) {
            $table->dropIndex('idx_dealer_code');
        });

        // Drop index from ms_menus.menu_code
        Schema::table('ms_menus', function (Blueprint $table) {
            $table->dropIndex('idx_menu_code');
        });

        // Drop index from ms_permissions.permission_code
        Schema::table('ms_permissions', function (Blueprint $table) {
            $table->dropIndex('idx_permission_code');
        });

        // Drop index from ms_role.role_code
        Schema::table('ms_role', function (Blueprint $table) {
            $table->dropIndex('idx_role_code');
        });

        // Drop composite index from ms_role_menus
        Schema::table('ms_role_menus', function (Blueprint $table) {
            $table->dropIndex('idx_role_menu');
        });
    }
};
