<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportHistoryMenuSeeder extends Seeder
{
    public function run(): void
    {
        $adminUserId = DB::table('ms_users')->where('email', 'admin@example.com')->value('user_id');

        // Generate menu ID
        $menuId = 'MNU' . str_pad((DB::table('ms_menus')->count() + 1), 5, '0', STR_PAD_LEFT);

        // Insert menu for Import History
        DB::table('ms_menus')->insert([
            'menu_id' => $menuId,
            'menu_code' => 'import-history',
            'menu_name' => 'Import History',
            'menu_url' => '/import-history',
            'menu_icon' => 'bi bi-file-earmark-arrow-up',
            'parent_id' => null,
            'menu_order' => 101,
            'is_active' => '1',
            'created_by' => $adminUserId,
            'created_date' => now(),
            'unique_id' => (string) Str::uuid(),
        ]);

        echo "Import History menu created successfully!\n";
        
        // Get the menu ID
        $menu = DB::table('ms_menus')
            ->where('menu_id', $menuId)
            ->first();

        if (!$menu) {
            echo "Error: Menu not found after creation!\n";
            return;
        }

        // Assign menu to Administrator role (role_id = 1)
        DB::table('ms_role_menus')->insert([
            'role_menu_id' => 'RMN' . str_pad((DB::table('ms_role_menus')->count() + 1), 5, '0', STR_PAD_LEFT),
            'role_id' => 'ROL00001',
            'menu_id' => $menu->menu_id,
            'created_by' => $adminUserId,
            'created_date' => now(),
            'unique_id' => (string) Str::uuid(),
        ]);

        echo "Menu assigned to Administrator role successfully!\n";
    }
}
