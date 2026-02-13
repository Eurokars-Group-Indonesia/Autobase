<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;

class UpdateMenuStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUserId = DB::table('ms_users')->where('email', 'admin@example.com')->value('user_id');

        // ========================================
        // 1. Tambahkan Permission Baru
        // ========================================
        
        $newPermissions = [
            [
                'permission_code' => 'import-history.view',
                'permission_name' => 'View Import History',
            ],
            [
                'permission_code' => 'transaction-body.view',
                'permission_name' => 'View Transaction Body',
            ],
        ];

        $createdPermissions = [];
        $permissionCounter = DB::table('ms_permissions')->count() + 1;
        foreach ($newPermissions as $permData) {
            // Check if permission already exists
            $existing = Permission::where('permission_code', $permData['permission_code'])->first();
            
            if (!$existing) {
                $permissionId = 'PRM' . str_pad($permissionCounter++, 5, '0', STR_PAD_LEFT);
                $permission = Permission::create([
                    'permission_id' => $permissionId,
                    'permission_code' => $permData['permission_code'],
                    'permission_name' => $permData['permission_name'],
                    'unique_id' => (string) Str::uuid(),
                    'created_by' => $adminUserId,
                    'is_active' => '1',
                ]);
                $createdPermissions[] = $permission;
                echo "✅ Created permission: {$permData['permission_code']}\n";
            } else {
                $createdPermissions[] = $existing;
                echo "ℹ️  Permission already exists: {$permData['permission_code']}\n";
            }
        }

        // ========================================
        // 2. Update Struktur Menu
        // ========================================

        // Hapus menu lama yang tidak sesuai struktur baru (opsional, bisa di-comment jika tidak ingin menghapus)
        // Menu::whereIn('menu_code', ['brands', 'dealers', 'transactions', 'search-history', 'import-history'])->delete();

        // Struktur Menu Baru:
        // 2. Master
        //    2.1 Brands
        //    2.2 Dealers
        // 3. Transactions
        //    3.1 Header Transaction
        //    3.2 Body Transaction
        // 4. History
        //    4.1 Search History
        //    4.2 Import History

        // ========================================
        // 2. Master (Parent Menu)
        // ========================================
        $masterMenu = Menu::where('menu_code', 'master')->first();
        if (!$masterMenu) {
            $menuId = 'MNU' . str_pad((DB::table('ms_menus')->count() + 1), 5, '0', STR_PAD_LEFT);
            $masterMenu = Menu::create([
                'menu_id' => $menuId,
                'menu_code' => 'master',
                'menu_name' => 'Master',
                'menu_url' => null,
                'menu_icon' => 'bi-database',
                'parent_id' => null,
                'menu_order' => 2,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Master\n";
        } else {
            $masterMenu->update([
                'menu_order' => 2,
                'updated_by' => $adminUserId
            ]);
            echo "ℹ️  Menu already exists: Master\n";
        }

        // 2.1 Brands
        $brandsMenu = Menu::where('menu_code', 'brands')->first();
        if ($brandsMenu) {
            $brandsMenu->update([
                'parent_id' => $masterMenu->menu_id,
                'menu_order' => 1,
                'updated_by' => $adminUserId
            ]);
            echo "✅ Updated menu: Brands (moved under Master)\n";
        } else {
            $menuId = 'MNU' . str_pad((DB::table('ms_menus')->count() + 1), 5, '0', STR_PAD_LEFT);
            Menu::create([
                'menu_id' => $menuId,
                'menu_code' => 'brands',
                'menu_name' => 'Brands',
                'menu_url' => '/brands',
                'menu_icon' => 'bi-tag',
                'parent_id' => $masterMenu->menu_id,
                'menu_order' => 1,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Brands\n";
        }

        // 2.2 Dealers
        $dealersMenu = Menu::where('menu_code', 'dealers')->first();
        if ($dealersMenu) {
            $dealersMenu->update([
                'parent_id' => $masterMenu->menu_id,
                'menu_order' => 2,
                'updated_by' => $adminUserId
            ]);
            echo "✅ Updated menu: Dealers (moved under Master)\n";
        } else {
            $menuId = 'MNU' . str_pad((DB::table('ms_menus')->count() + 1), 5, '0', STR_PAD_LEFT);
            Menu::create([
                'menu_id' => $menuId,
                'menu_code' => 'dealers',
                'menu_name' => 'Dealers',
                'menu_url' => '/dealers',
                'menu_icon' => 'bi-shop',
                'parent_id' => $masterMenu->menu_id,
                'menu_order' => 2,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Dealers\n";
        }

        // ========================================
        // 3. Transactions (Parent Menu)
        // ========================================
        $transactionsMenu = Menu::where('menu_code', 'transactions')->first();
        if (!$transactionsMenu) {
            $menuId = 'MNU' . str_pad((DB::table('ms_menus')->count() + 1), 5, '0', STR_PAD_LEFT);
            $transactionsMenu = Menu::create([
                'menu_id' => $menuId,
                'menu_code' => 'transactions',
                'menu_name' => 'Transactions',
                'menu_url' => null,
                'menu_icon' => 'bi-receipt',
                'parent_id' => null,
                'menu_order' => 3,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Transactions\n";
        } else {
            $transactionsMenu->update([
                'menu_url' => null, // Make it parent menu
                'menu_order' => 3,
                'updated_by' => $adminUserId
            ]);
            echo "✅ Updated menu: Transactions (converted to parent menu)\n";
        }

        // 3.1 Header Transaction (Transaction Header)
        $masterTransactionMenu = Menu::where('menu_code', 'header-transaction')->first();
        if (!$masterTransactionMenu) {
            $menuId = 'MNU' . str_pad((DB::table('ms_menus')->count() + 1), 5, '0', STR_PAD_LEFT);
            Menu::create([
                'menu_id' => $menuId,
                'menu_code' => 'header-transaction',
                'menu_name' => 'Header Transaction',
                'menu_url' => '/transactions',
                'menu_icon' => 'bi-file-earmark-text',
                'parent_id' => $transactionsMenu->menu_id,
                'menu_order' => 1,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Header Transaction\n";
        } else {
            echo "ℹ️  Menu already exists: Header Transaction\n";
        }

        // 3.2 Body Transaction (Transaction Body)
        $detailTransactionMenu = Menu::where('menu_code', 'body-transaction')->first();
        if (!$detailTransactionMenu) {
            $menuId = 'MNU' . str_pad((DB::table('ms_menus')->count() + 1), 5, '0', STR_PAD_LEFT);
            Menu::create([
                'menu_id' => $menuId,
                'menu_code' => 'body-transaction',
                'menu_name' => 'Body Transaction',
                'menu_url' => '/transaction-body',
                'menu_icon' => 'bi-list-ul',
                'parent_id' => $transactionsMenu->menu_id,
                'menu_order' => 2,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Body Transaction\n";
        } else {
            echo "ℹ️  Menu already exists: Body Transaction\n";
        }

        // ========================================
        // 4. History (Parent Menu)
        // ========================================
        $historyMenu = Menu::where('menu_code', 'history')->first();
        if (!$historyMenu) {
            $menuId = 'MNU' . str_pad((DB::table('ms_menus')->count() + 1), 5, '0', STR_PAD_LEFT);
            $historyMenu = Menu::create([
                'menu_id' => $menuId,
                'menu_code' => 'history',
                'menu_name' => 'History',
                'menu_url' => null,
                'menu_icon' => 'bi-clock-history',
                'parent_id' => null,
                'menu_order' => 4,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: History\n";
        } else {
            echo "ℹ️  Menu already exists: History\n";
        }

        // 4.1 Search History
        $searchHistoryMenu = Menu::where('menu_code', 'search-history')->first();
        if ($searchHistoryMenu) {
            $searchHistoryMenu->update([
                'parent_id' => $historyMenu->menu_id,
                'menu_order' => 1,
                'updated_by' => $adminUserId
            ]);
            echo "✅ Updated menu: Search History (moved under History)\n";
        } else {
            $menuId = 'MNU' . str_pad((DB::table('ms_menus')->count() + 1), 5, '0', STR_PAD_LEFT);
            Menu::create([
                'menu_id' => $menuId,
                'menu_code' => 'search-history',
                'menu_name' => 'Search History',
                'menu_url' => '/search-history',
                'menu_icon' => 'bi-search',
                'parent_id' => $historyMenu->menu_id,
                'menu_order' => 1,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Search History\n";
        }

        // 4.2 Import History
        $importHistoryMenu = Menu::where('menu_code', 'import-history')->first();
        if ($importHistoryMenu) {
            $importHistoryMenu->update([
                'parent_id' => $historyMenu->menu_id,
                'menu_order' => 2,
                'updated_by' => $adminUserId
            ]);
            echo "✅ Updated menu: Import History (moved under History)\n";
        } else {
            $menuId = 'MNU' . str_pad((DB::table('ms_menus')->count() + 1), 5, '0', STR_PAD_LEFT);
            Menu::create([
                'menu_id' => $menuId,
                'menu_code' => 'import-history',
                'menu_name' => 'Import History',
                'menu_url' => '/import-history',
                'menu_icon' => 'bi-file-earmark-arrow-up',
                'parent_id' => $historyMenu->menu_id,
                'menu_order' => 2,
                'unique_id' => (string) Str::uuid(),
                'created_by' => $adminUserId,
                'is_active' => '1',
            ]);
            echo "✅ Created menu: Import History\n";
        }

        // ========================================
        // 3. Assign Permissions & Menus to Admin Role
        // ========================================
        
        $adminRole = Role::where('role_code', 'ADMIN')->first();
        
        if ($adminRole) {
            // Assign new permissions to admin role
            $rolePermissionCounter = DB::table('ms_role_permissions')->count() + 1;
            foreach ($createdPermissions as $permission) {
                $exists = DB::table('ms_role_permissions')
                    ->where('role_id', $adminRole->role_id)
                    ->where('permission_id', $permission->permission_id)
                    ->exists();
                
                if (!$exists) {
                    DB::table('ms_role_permissions')->insert([
                        'role_permission_id' => 'RPM' . str_pad($rolePermissionCounter++, 5, '0', STR_PAD_LEFT),
                        'role_id' => $adminRole->role_id,
                        'permission_id' => $permission->permission_id,
                        'unique_id' => (string) Str::uuid(),
                        'created_by' => $adminUserId,
                        'created_date' => now(),
                        'is_active' => '1',
                    ]);
                    echo "✅ Assigned permission to Admin: {$permission->permission_code}\n";
                }
            }

            // Assign new menus to admin role
            $newMenus = Menu::whereIn('menu_code', [
                'master', 'history', 'header-transaction', 'body-transaction'
            ])->get();

            $roleMenuCounter = DB::table('ms_role_menus')->count() + 1;
            foreach ($newMenus as $menu) {
                $exists = DB::table('ms_role_menus')
                    ->where('role_id', $adminRole->role_id)
                    ->where('menu_id', $menu->menu_id)
                    ->exists();
                
                if (!$exists) {
                    DB::table('ms_role_menus')->insert([
                        'role_menu_id' => 'RMN' . str_pad($roleMenuCounter++, 5, '0', STR_PAD_LEFT),
                        'role_id' => $adminRole->role_id,
                        'menu_id' => $menu->menu_id,
                        'unique_id' => (string) Str::uuid(),
                        'created_by' => $adminUserId,
                        'created_date' => now(),
                        'is_active' => '1',
                    ]);
                    echo "✅ Assigned menu to Admin: {$menu->menu_name}\n";
                }
            }
        }

        echo "\n✅ Menu structure updated successfully!\n";
        echo "\nFinal Menu Structure:\n";
        echo "1. User Management\n";
        echo "   1.1 Users\n";
        echo "   1.2 Roles\n";
        echo "   1.3 Permissions\n";
        echo "   1.4 Menus\n";
        echo "2. Master\n";
        echo "   2.1 Brands\n";
        echo "   2.2 Dealers\n";
        echo "3. Transactions\n";
        echo "   3.1 Header Transaction\n";
        echo "   3.2 Body Transaction\n";
        echo "4. History\n";
        echo "   4.1 Search History\n";
        echo "   4.2 Import History\n";
    }
}
