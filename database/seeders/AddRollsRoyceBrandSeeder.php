<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AddRollsRoyceBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUserId = DB::table('ms_users')->where('email', 'admin@example.com')->value('user_id');
        
        if (!$adminUserId) {
            $this->command->error('Admin user not found. Please run RBACSeeder first.');
            return;
        }

        // Check if Rolls-Royce brand already exists
        $existingBrand = DB::table('ms_brand')
            ->where('brand_code', '08')
            ->orWhere('brand_name', 'Rolls-Royce')
            ->first();

        if ($existingBrand) {
            $this->command->info('Rolls-Royce brand already exists.');
            $brandId = $existingBrand->brand_id;
        } else {
            // Insert Rolls-Royce brand
            $brandId = 'BRD' . str_pad((DB::table('ms_brand')->count() + 1), 5, '0', STR_PAD_LEFT);
            
            DB::table('ms_brand')->insert([
                'brand_id' => $brandId,
                'brand_code' => '08',
                'brand_name' => 'Rolls-Royce',
                'brand_group' => 'Volkswagen',
                'country_origin' => 'Germany',
                'created_by' => $adminUserId,
                'created_date' => now(),
                'unique_id' => (string) Str::uuid(),
                'is_active' => '1',
            ]);
            
            $this->command->info('✅ Created brand: Rolls-Royce (ID: ' . $brandId . ')');
        }

        // Assign Rolls-Royce brand to Admin user
        $existingUserBrand = DB::table('ms_user_brand')
            ->where('user_id', $adminUserId)
            ->where('brand_id', $brandId)
            ->first();

        if (!$existingUserBrand) {
            DB::table('ms_user_brand')->insert([
                'user_brand_id' => 'UBD' . str_pad((DB::table('ms_user_brand')->count() + 1), 5, '0', STR_PAD_LEFT),
                'user_id' => $adminUserId,
                'brand_id' => $brandId,
                'created_by' => $adminUserId,
                'created_date' => now(),
                'unique_id' => (string) Str::uuid(),
                'is_active' => '1',
            ]);
            
            $this->command->info('✅ Assigned Rolls-Royce brand to Admin user');
        } else {
            $this->command->info('ℹ️  Admin user already has Rolls-Royce brand');
        }

        // Display summary
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('  Rolls-Royce Brand Seeder Complete');
        $this->command->info('========================================');
        $this->command->info('Brand ID: ' . $brandId);
        $this->command->info('Brand Code: 08');
        $this->command->info('Brand Name: Rolls-Royce');
        $this->command->info('Assigned to: Admin user');
        $this->command->info('========================================');
    }
}
