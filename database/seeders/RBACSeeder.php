<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;

class RBACSeeder extends Seeder
{
    public function run(): void
    {
        // ========================================
        // 1. Create SUPER ADMIN User FIRST (USR00001)
        // ========================================
        $superAdminUserId = 'USR00001';
        $superAdminUser = User::create([
            'user_id' => $superAdminUserId,
            'name' => 'superadmin',
            'email' => 'it.developer@eurokars.co.id',
            'full_name' => 'Super Administrator',
            'password' => Hash::make('Eg1SuperAdmin123!'),
            'unique_id' => (string) Str::uuid(),
            'is_active' => '1',
        ]);

        // ========================================
        // 2. Create Permissions
        // ========================================
        $permissions = [
            ['permission_code' => 'users.view', 'permission_name' => 'View Users'],
            ['permission_code' => 'users.create', 'permission_name' => 'Create Users'],
            ['permission_code' => 'users.edit', 'permission_name' => 'Edit Users'],
            ['permission_code' => 'users.delete', 'permission_name' => 'Delete Users'],
            ['permission_code' => 'roles.view', 'permission_name' => 'View Roles'],
            ['permission_code' => 'roles.create', 'permission_name' => 'Create Roles'],
            ['permission_code' => 'roles.edit', 'permission_name' => 'Edit Roles'],
            ['permission_code' => 'roles.delete', 'permission_name' => 'Delete Roles'],
            ['permission_code' => 'permissions.view', 'permission_name' => 'View Permissions'],
            ['permission_code' => 'permissions.create', 'permission_name' => 'Create Permissions'],
            ['permission_code' => 'permissions.edit', 'permission_name' => 'Edit Permissions'],
            ['permission_code' => 'permissions.delete', 'permission_name' => 'Delete Permissions'],
            ['permission_code' => 'menus.view', 'permission_name' => 'View Menus'],
            ['permission_code' => 'menus.create', 'permission_name' => 'Create Menus'],
            ['permission_code' => 'menus.edit', 'permission_name' => 'Edit Menus'],
            ['permission_code' => 'menus.delete', 'permission_name' => 'Delete Menus'],
        ];

        $createdPermissions = [];
        $permissionCounter = 1;
        foreach ($permissions as $permission) {
            $permissionId = 'PRM' . str_pad($permissionCounter, 5, '0', STR_PAD_LEFT);
            $createdPermissions[] = Permission::create([
                'permission_id' => $permissionId,
                'permission_code' => $permission['permission_code'],
                'permission_name' => $permission['permission_name'],
                'unique_id' => (string) Str::uuid(),
                'created_by' => $superAdminUser->user_id,
                'is_active' => '1',
            ]);
            $permissionCounter++;
        }

        // ========================================
        // 3. Create Menus
        // ========================================
        $menuCounter = 1;
        $userManagementId = 'MNU' . str_pad($menuCounter++, 5, '0', STR_PAD_LEFT);
        $userManagement = Menu::create([
            'menu_id' => $userManagementId,
            'menu_code' => 'user_management',
            'menu_name' => 'User Management',
            'menu_url' => null,
            'menu_icon' => 'bi-people',
            'parent_id' => null,
            'menu_order' => 1,
            'unique_id' => (string) Str::uuid(),
            'created_by' => $superAdminUser->user_id,
            'is_active' => '1',
        ]);

        Menu::create([
            'menu_id' => 'MNU' . str_pad($menuCounter++, 5, '0', STR_PAD_LEFT),
            'menu_code' => 'users',
            'menu_name' => 'Users',
            'menu_url' => '/users',
            'menu_icon' => 'bi-person',
            'parent_id' => $userManagement->menu_id,
            'menu_order' => 1,
            'unique_id' => (string) Str::uuid(),
            'created_by' => $superAdminUser->user_id,
            'is_active' => '1',
        ]);

        Menu::create([
            'menu_id' => 'MNU' . str_pad($menuCounter++, 5, '0', STR_PAD_LEFT),
            'menu_code' => 'roles',
            'menu_name' => 'Roles',
            'menu_url' => '/roles',
            'menu_icon' => 'bi-shield-check',
            'parent_id' => $userManagement->menu_id,
            'menu_order' => 2,
            'unique_id' => (string) Str::uuid(),
            'created_by' => $superAdminUser->user_id,
            'is_active' => '1',
        ]);

        Menu::create([
            'menu_id' => 'MNU' . str_pad($menuCounter++, 5, '0', STR_PAD_LEFT),
            'menu_code' => 'permissions',
            'menu_name' => 'Permissions',
            'menu_url' => '/permissions',
            'menu_icon' => 'bi-key',
            'parent_id' => $userManagement->menu_id,
            'menu_order' => 3,
            'unique_id' => (string) Str::uuid(),
            'created_by' => $superAdminUser->user_id,
            'is_active' => '1',
        ]);

        Menu::create([
            'menu_id' => 'MNU' . str_pad($menuCounter++, 5, '0', STR_PAD_LEFT),
            'menu_code' => 'menus',
            'menu_name' => 'Menus',
            'menu_url' => '/menus',
            'menu_icon' => 'bi-menu-button-wide',
            'parent_id' => $userManagement->menu_id,
            'menu_order' => 4,
            'unique_id' => (string) Str::uuid(),
            'created_by' => $superAdminUser->user_id,
            'is_active' => '1',
        ]);

        // ========================================
        // 4. Create SUPER ADMIN Role (ROL00001)
        // ========================================
        $superAdminRoleId = 'ROL00001';
        $superAdminRole = Role::create([
            'role_id' => $superAdminRoleId,
            'role_code' => 'SUPERADMIN',
            'role_name' => 'Super Admin',
            'role_description' => 'Super Administrator with full system access (Hidden from UI)',
            'unique_id' => (string) Str::uuid(),
            'created_by' => $superAdminUser->user_id,
            'is_active' => '1',
        ]);

        // Attach all permissions to SUPER ADMIN role
        $rolePermissionCounter = 1;
        foreach ($createdPermissions as $permission) {
            $superAdminRole->permissions()->attach($permission->permission_id, [
                'role_permission_id' => 'RPM' . str_pad($rolePermissionCounter++, 5, '0', STR_PAD_LEFT),
                'unique_id' => (string) Str::uuid(),
                'created_by' => $superAdminUser->user_id,
                'created_date' => now(),
                'is_active' => '1',
            ]);
        }

        // Attach all menus to SUPER ADMIN role
        $allMenus = Menu::all();
        $roleMenuCounter = 1;
        foreach ($allMenus as $menu) {
            $superAdminRole->menus()->attach($menu->menu_id, [
                'role_menu_id' => 'RMN' . str_pad($roleMenuCounter++, 5, '0', STR_PAD_LEFT),
                'unique_id' => (string) Str::uuid(),
                'created_by' => $superAdminUser->user_id,
                'created_date' => now(),
                'is_active' => '1',
            ]);
        }

        // Assign SUPER ADMIN role to SUPER ADMIN user
        $superAdminUser->roles()->attach($superAdminRole->role_id, [
            'user_role_id' => 'URO00001',
            'unique_id' => (string) Str::uuid(),
            'assigned_date' => now(),
            'created_by' => $superAdminUser->user_id,
            'created_date' => now(),
            'is_active' => '1',
        ]);

        // ========================================
        // 5. Create ADMIN User (USR00002)
        // ========================================
        $adminUserId = 'USR00002';
        $adminUser = User::create([
            'user_id' => $adminUserId,
            'name' => 'admin',
            'email' => 'admin@example.com',
            'full_name' => 'System Administrator',
            'password' => Hash::make('password'),
            'unique_id' => (string) Str::uuid(),
            'created_by' => $superAdminUser->user_id,
            'is_active' => '1',
        ]);

        // ========================================
        // 6. Create ADMIN Role (ROL00002)
        // ========================================
        $adminRoleId = 'ROL00002';
        $adminRole = Role::create([
            'role_id' => $adminRoleId,
            'role_code' => 'ADMIN',
            'role_name' => 'Administrator',
            'role_description' => 'Administrator',
            'unique_id' => (string) Str::uuid(),
            'created_by' => $superAdminUser->user_id,
            'is_active' => '1',
        ]);

        // Attach permissions to ADMIN role (exclude permissions.* and menus.*)
        foreach ($createdPermissions as $permission) {
            // Skip permissions and menus management permissions
            if (str_starts_with($permission->permission_code, 'permissions.') || 
                str_starts_with($permission->permission_code, 'menus.')) {
                continue;
            }
            
            $adminRole->permissions()->attach($permission->permission_id, [
                'role_permission_id' => 'RPM' . str_pad($rolePermissionCounter++, 5, '0', STR_PAD_LEFT),
                'unique_id' => (string) Str::uuid(),
                'created_by' => $superAdminUser->user_id,
                'created_date' => now(),
                'is_active' => '1',
            ]);
        }

        // Attach menus to ADMIN role (exclude Permissions and Menus menu)
        foreach ($allMenus as $menu) {
            // Skip Permissions and Menus menu
            if ($menu->menu_code === 'permissions' || $menu->menu_code === 'menus') {
                continue;
            }
            
            $adminRole->menus()->attach($menu->menu_id, [
                'role_menu_id' => 'RMN' . str_pad($roleMenuCounter++, 5, '0', STR_PAD_LEFT),
                'unique_id' => (string) Str::uuid(),
                'created_by' => $superAdminUser->user_id,
                'created_date' => now(),
                'is_active' => '1',
            ]);
        }

        // Assign ADMIN role to ADMIN user
        $adminUser->roles()->attach($adminRole->role_id, [
            'user_role_id' => 'URO00002',
            'unique_id' => (string) Str::uuid(),
            'assigned_date' => now(),
            'created_by' => $superAdminUser->user_id,
            'created_date' => now(),
            'is_active' => '1',
        ]);

        // ========================================
        // Summary
        // ========================================
        echo "\n";
        echo "========================================\n";
        echo "RBAC SETUP COMPLETED\n";
        echo "========================================\n";
        echo "SUPER ADMIN:\n";
        echo "  Email: superadmin@system.local\n";
        echo "  Password: SuperAdmin@123!\n";
        echo "  Access: ALL menus & permissions\n";
        echo "  (Hidden from UI)\n";
        echo "\n";
        echo "ADMIN:\n";
        echo "  Email: admin@example.com\n";
        echo "  Password: password\n";
        echo "  Access: ALL menus & permissions\n";
        echo "  Except: Permissions & Menus management\n";
        echo "========================================\n";
        echo "\n";
    }
}
