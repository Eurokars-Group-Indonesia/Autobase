<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CounterNumberSeeder::class,
            SequenceSeeder::class,
            RBACSeeder::class, // Creates SUPER ADMIN (USR00001, ROL00001) and ADMIN (USR00002, ROL00002)
            BrandSeeder::class,
            AddRollsRoyceBrandSeeder::class,
            DealerSeeder::class,
            ImportHistoryMenuSeeder::class,
            ImportHistoryPermissionSeeder::class,
            SearchHistoryMenuSeeder::class,
            SearchHistoryPermissionSeeder::class,
            TransactionBodyImportPermissionSeeder::class,
            // TransactionBodySeeder::class,
            TransactionHeaderSeeder::class,
            TransactionImportPermissionSeeder::class,
            UpdateMenuStructureSeeder::class,
            FinalSyncSuperAdminSeeder::class, // MUST BE LAST - Sync ALL permissions & menus to SUPER ADMIN
        ]);
    }
}
