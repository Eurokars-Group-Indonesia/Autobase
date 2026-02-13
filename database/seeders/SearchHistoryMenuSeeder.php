<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchHistoryMenuSeeder extends Seeder
{
    public function run(): void
    {
        $adminUserId = DB::table('ms_users')->where('email', 'admin@example.com')->value('user_id');

        // Generate menu ID
        $menuId = 'MNU' . str_pad((DB::table('ms_menus')->count() + 1), 5, '0', STR_PAD_LEFT);

        // Insert menu for Search History
        DB::table('ms_menus')->insert([
            'menu_id' => $menuId,
            'menu_code' => 'search-history',
            'menu_name' => 'Search History',
            'menu_url' => '/search-history',
            'menu_icon' => 'bi bi-clock-history',
            'parent_id' => null,
            'menu_order' => 100,
            'is_active' => '1',
            'created_by' => $adminUserId,
            'created_date' => now(),
            'unique_id' => (string) Str::uuid(),
        ]);

        echo "Search History menu created successfully!\n";
        
        // Get the menu ID
        $menu = DB::table('ms_menus')
            ->where('menu_id', $menuId)
            ->first();

        if (!$menu) {
            echo "Error: Menu not found after creation!\n";
            return;
        }

        // Assign menu to Administrator role
        DB::table('ms_role_menus')->insert([
            'role_menu_id' => 'RMN' . str_pad((DB::table('ms_role_menus')->count() + 1), 5, '0', STR_PAD_LEFT),
            'role_id' => 'ROL00001',
            'menu_id' => $menu->menu_id,
            'created_by' => $adminUserId,
            'created_date' => now(),
            'unique_id' => (string) Str::uuid(),
            'is_active' => '1',
        ]);

        echo "Menu assigned to Administrator role successfully!\n";
    }
}
