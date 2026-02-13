<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE TRIGGER `tg_sequence_AFTER_UPDATE` AFTER UPDATE ON `sq_sequence` FOR EACH ROW 
            BEGIN
                UPDATE ms_counter_number
                SET seq_flg = NEW.seq_value
                WHERE screen_id = NEW.screen_id;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `tg_sequence_AFTER_UPDATE`");
    }
};
