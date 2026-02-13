<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinalSyncSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder runs LAST to ensure SUPER ADMIN has ALL permissions and menus
     * that were added by other seeders.
     */
    public function run(): void
    {
        $superAdminRoleId = 'ROL00001';
        $superAdminUserId = 'USR00001';
        
        // Check if SUPER ADMIN role exists
        $superAdminRole = DB::table('ms_role')
            ->where('role_id', $superAdminRoleId)
            ->where('role_code', 'SUPERADMIN')
            ->first();
            
        if (!$superAdminRole) {
            $this->command->error('SUPER ADMIN role not found. Please run RBACSeeder first.');
            return;
        }
        
        $this->command->info('Syncing ALL permissions and menus to SUPER ADMIN...');
        
        // ========================================
        // 1. Sync ALL Permissions to SUPER ADMIN
        // ========================================
        $allPermissions = DB::table('ms_permissions')->where('is_active', '1')->get();
        $existingPermissions = DB::table('ms_role_permissions')
            ->where('role_id', $superAdminRoleId)
            ->where('is_active', '1')
            ->pluck('permission_id')
            ->toArray();
        
        // Get last role_permission_id to continue numbering
        $lastRolePermission = DB::table('ms_role_permissions')
            ->orderBy('role_permission_id', 'desc')
            ->first();
        
        $rolePermissionCounter = 1;
        if ($lastRolePermission && preg_match('/RPM(\d+)/', $lastRolePermission->role_permission_id, $matches)) {
            $rolePermissionCounter = (int)$matches[1] + 1;
        }
        
        $newPermissionsCount = 0;
        foreach ($allPermissions as $permission) {
            if (!in_array($permission->permission_id, $existingPermissions)) {
                DB::table('ms_role_permissions')->insert([
                    'role_permission_id' => 'RPM' . str_pad($rolePermissionCounter++, 5, '0', STR_PAD_LEFT),
                    'role_id' => $superAdminRoleId,
                    'permission_id' => $permission->permission_id,
                    'created_by' => $superAdminUserId,
                    'created_date' => now(),
                    'updated_by' => $superAdminUserId,
                    'updated_date' => now(),
                    'unique_id' => (string) Str::uuid(),
                    'is_active' => '1',
                ]);
                $newPermissionsCount++;
            }
        }
        
        $this->command->info("✓ Synced {$newPermissionsCount} new permissions to SUPER ADMIN");
        $this->command->info("  Total permissions: " . count($allPermissions));
        
        // ========================================
        // 2. Sync ALL Menus to SUPER ADMIN
        // ========================================
        $allMenus = DB::table('ms_menus')->where('is_active', '1')->get();
        $existingMenus = DB::table('ms_role_menus')
            ->where('role_id', $superAdminRoleId)
            ->where('is_active', '1')
            ->pluck('menu_id')
            ->toArray();
        
        // Get last role_menu_id to continue numbering
        $lastRoleMenu = DB::table('ms_role_menus')
            ->orderBy('role_menu_id', 'desc')
            ->first();
        
        $roleMenuCounter = 1;
        if ($lastRoleMenu && preg_match('/RMN(\d+)/', $lastRoleMenu->role_menu_id, $matches)) {
            $roleMenuCounter = (int)$matches[1] + 1;
        }
        
        $newMenusCount = 0;
        foreach ($allMenus as $menu) {
            if (!in_array($menu->menu_id, $existingMenus)) {
                DB::table('ms_role_menus')->insert([
                    'role_menu_id' => 'RMN' . str_pad($roleMenuCounter++, 5, '0', STR_PAD_LEFT),
                    'role_id' => $superAdminRoleId,
                    'menu_id' => $menu->menu_id,
                    'created_by' => $superAdminUserId,
                    'created_date' => now(),
                    'updated_by' => $superAdminUserId,
                    'updated_date' => now(),
                    'unique_id' => (string) Str::uuid(),
                    'is_active' => '1',
                ]);
                $newMenusCount++;
            }
        }
        
        $this->command->info("✓ Synced {$newMenusCount} new menus to SUPER ADMIN");
        $this->command->info("  Total menus: " . count($allMenus));
        
        // ========================================
        // 3. Sync ALL Brands to SUPER ADMIN User
        // ========================================
        $superAdminUser = DB::table('ms_users')
            ->where('user_id', $superAdminUserId)
            ->first();
            
        if ($superAdminUser) {
            $allBrands = DB::table('ms_brand')->where('is_active', '1')->get();
            $existingBrands = DB::table('ms_user_brand')
                ->where('user_id', $superAdminUserId)
                ->where('is_active', '1')
                ->pluck('brand_id')
                ->toArray();
            
            // Get last user_brand_id to continue numbering
            $lastUserBrand = DB::table('ms_user_brand')
                ->orderBy('user_brand_id', 'desc')
                ->first();
            
            $userBrandCounter = 1;
            if ($lastUserBrand && preg_match('/UBD(\d+)/', $lastUserBrand->user_brand_id, $matches)) {
                $userBrandCounter = (int)$matches[1] + 1;
            }
            
            $newBrandsCount = 0;
            foreach ($allBrands as $brand) {
                if (!in_array($brand->brand_id, $existingBrands)) {
                    DB::table('ms_user_brand')->insert([
                        'user_brand_id' => 'UBD' . str_pad($userBrandCounter++, 5, '0', STR_PAD_LEFT),
                        'user_id' => $superAdminUserId,
                        'brand_id' => $brand->brand_id,
                        'created_by' => $superAdminUserId,
                        'created_date' => now(),
                        'updated_by' => $superAdminUserId,
                        'updated_date' => now(),
                        'unique_id' => (string) Str::uuid(),
                        'is_active' => '1',
                    ]);
                    $newBrandsCount++;
                }
            }
            
            $this->command->info("✓ Synced {$newBrandsCount} new brands to SUPER ADMIN user");
            $this->command->info("  Total brands: " . count($allBrands));
        }
        
        // ========================================
        // Summary
        // ========================================
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('SUPER ADMIN FINAL SYNC COMPLETED');
        $this->command->info('========================================');
        $this->command->info('SUPER ADMIN now has:');
        $this->command->info("  - " . count($allPermissions) . " permissions");
        $this->command->info("  - " . count($allMenus) . " menus");
        if (isset($allBrands)) {
            $this->command->info("  - " . count($allBrands) . " brands");
        }
        $this->command->info('========================================');
        $this->command->info('');
    }
}
