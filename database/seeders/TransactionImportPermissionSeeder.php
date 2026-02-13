<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionImportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminUserId = DB::table('ms_users')->where('email', 'admin@example.com')->value('user_id');

        // Generate permission ID
        $permissionId = 'PRM' . str_pad((DB::table('ms_permissions')->count() + 1), 5, '0', STR_PAD_LEFT);

        // Insert permission for transaction import
        DB::table('ms_permissions')->insert([
            'permission_id' => $permissionId,
            'permission_code' => 'transactions.header.import',
            'permission_name' => 'Import Transaction Headers',
            'is_active' => '1',
            'created_by' => $adminUserId,
            'created_date' => now(),
            'updated_by' => $adminUserId,
            'updated_date' => now(),
            'unique_id' => (string) Str::uuid(),
        ]);

        echo "Transaction header import permission created successfully!\n";
        
        // Get the permission ID
        $permission = DB::table('ms_permissions')
            ->where('permission_id', $permissionId)
            ->first();

        if ($permission) {
            // Assign to Administrator role
            DB::table('ms_role_permissions')->insert([
                'role_permission_id' => 'RPM' . str_pad((DB::table('ms_role_permissions')->count() + 1), 5, '0', STR_PAD_LEFT),
                'role_id' => 'ROL00001',
                'permission_id' => $permission->permission_id,
                'created_by' => $adminUserId,
                'created_date' => now(),
                'updated_by' => $adminUserId,
                'updated_date' => now(),
                'unique_id' => (string) Str::uuid(),
                'is_active' => '1',
            ]);

            echo "Permission assigned to Administrator role!\n";
        }
    }
}
