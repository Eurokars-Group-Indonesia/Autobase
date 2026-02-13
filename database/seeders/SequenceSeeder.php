<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SequenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sequences = [
            [
                'id' => 1,
                'screen_id' => 'MBD01',
                'seq_name' => 'Master Brand',
                'seq_value' => 24,
            ],
            [
                'id' => 2,
                'screen_id' => 'MRO01',
                'seq_name' => 'Master Role',
                'seq_value' => 6,
            ],
            [
                'id' => 3,
                'screen_id' => 'MRP01',
                'seq_name' => 'Master Role Permission',
                'seq_value' => 1,
            ],
            [
                'id' => 4,
                'screen_id' => 'MPE01',
                'seq_name' => 'Master Permission',
                'seq_value' => 6,
            ],
            [
                'id' => 5,
                'screen_id' => 'MUS01',
                'seq_name' => 'Master User',
                'seq_value' => 3,
            ],
            [
                'id' => 6,
                'screen_id' => 'MUR01',
                'seq_name' => 'Master User Role',
                'seq_value' => 23,
            ],
            [
                'id' => 8,
                'screen_id' => 'ZLG01',
                'seq_name' => 'Login',
                'seq_value' => 1,
            ],
            [
                'id' => 9,
                'screen_id' => 'MDR01',
                'seq_name' => 'Master Dealers',
                'seq_value' => 2,
            ],
            [
                'id' => 10,
                'screen_id' => 'MUB01',
                'seq_name' => 'Master User Brands',
                'seq_value' => 3,
            ],
            [
                'id' => 61,
                'screen_id' => 'MRM01',
                'seq_name' => 'Master Role Menu',
                'seq_value' => 1,
            ],
            [
                'id' => 62,
                'screen_id' => 'MMN01',
                'seq_name' => 'Master Menu',
                'seq_value' => 15,
            ],
        ];

        foreach ($sequences as $sequence) {
            DB::table('sq_sequence')->insert($sequence);
        }

        $this->command->info('Sequence seeded successfully!');
    }
}
