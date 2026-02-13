<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DealerSeeder extends Seeder
{
    public function run(): void
    {
        $adminUserId = DB::table('ms_users')->where('email', 'admin@example.com')->value('user_id');
        
        if (!$adminUserId) {
            $this->command->error('Admin user not found. Please run RBACSeeder first.');
            return;
        }

        // Create Dealer Permissions
        $permissions = [
            ['permission_code' => 'dealers.view', 'permission_name' => 'View Dealers'],
            ['permission_code' => 'dealers.create', 'permission_name' => 'Create Dealer'],
            ['permission_code' => 'dealers.edit', 'permission_name' => 'Edit Dealer'],
            ['permission_code' => 'dealers.delete', 'permission_name' => 'Delete Dealer'],
        ];

        $permissionIds = [];
        foreach ($permissions as $permission) {
            $existingPermission = DB::table('ms_permissions')
                ->where('permission_code', $permission['permission_code'])
                ->first();

            if (!$existingPermission) {
                $permissionUniqueId = (string) Str::uuid();
                $permissionId = 'PRM' . str_pad((DB::table('ms_permissions')->count() + 1), 5, '0', STR_PAD_LEFT);
                
                DB::table('ms_permissions')->insert([
                    'permission_id' => $permissionId,
                    'permission_code' => $permission['permission_code'],
                    'permission_name' => $permission['permission_name'],
                    'created_by' => $adminUserId,
                    'created_date' => now(),
                    'unique_id' => $permissionUniqueId,
                    'is_active' => '1',
                ]);
                $permissionIds[] = $permissionId;
                $this->command->info("Created permission: {$permission['permission_code']}");
            } else {
                $permissionIds[] = $existingPermission->permission_id;
                $this->command->info("Permission already exists: {$permission['permission_code']}");
            }
        }

        // Create Dealer Menu
        $existingMenu = DB::table('ms_menus')->where('menu_code', 'dealers')->first();
        
        if (!$existingMenu) {
            $menuId = 'MNU' . str_pad((DB::table('ms_menus')->count() + 1), 5, '0', STR_PAD_LEFT);
            
            DB::table('ms_menus')->insert([
                'menu_id' => $menuId,
                'menu_code' => 'dealers',
                'menu_name' => 'Dealers',
                'menu_url' => '/dealers',
                'menu_icon' => 'bi-shop',
                'parent_id' => null,
                'menu_order' => 60,
                'created_by' => $adminUserId,
                'created_date' => now(),
                'unique_id' => (string) Str::uuid(),
                'is_active' => '1',
            ]);
            $this->command->info("Created menu: Dealers");
        } else {
            $menuId = $existingMenu->menu_id;
            $this->command->info("Menu already exists: Dealers");
        }

        // Attach permissions to Admin role
        $adminRoleId = DB::table('ms_role')->where('role_code', 'ADMIN')->value('role_id');
        
        if ($adminRoleId) {
            foreach ($permissionIds as $permissionId) {
                $existingRolePermission = DB::table('ms_role_permissions')
                    ->where('role_id', $adminRoleId)
                    ->where('permission_id', $permissionId)
                    ->first();

                if (!$existingRolePermission) {
                    DB::table('ms_role_permissions')->insert([
                        'role_permission_id' => 'RPM' . str_pad((DB::table('ms_role_permissions')->count() + 1), 5, '0', STR_PAD_LEFT),
                        'role_id' => $adminRoleId,
                        'permission_id' => $permissionId,
                        'created_by' => $adminUserId,
                        'created_date' => now(),
                        'unique_id' => (string) Str::uuid(),
                        'is_active' => '1',
                    ]);
                }
            }
            $this->command->info("Attached dealer permissions to Admin role");

            // Attach menu to Admin role
            $existingRoleMenu = DB::table('ms_role_menus')
                ->where('role_id', $adminRoleId)
                ->where('menu_id', $menuId)
                ->first();

            if (!$existingRoleMenu) {
                DB::table('ms_role_menus')->insert([
                    'role_menu_id' => 'RMN' . str_pad((DB::table('ms_role_menus')->count() + 1), 5, '0', STR_PAD_LEFT),
                    'role_id' => $adminRoleId,
                    'menu_id' => $menuId,
                    'created_by' => $adminUserId,
                    'created_date' => now(),
                    'unique_id' => (string) Str::uuid(),
                    'is_active' => '1',
                ]);
                $this->command->info("Attached dealer menu to Admin role");
            }
        }

        $this->command->info('Dealer module seeded successfully!');
    }
}
