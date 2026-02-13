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
            CREATE TRIGGER `tg_ms_dealers_AFTER_INSERT` AFTER INSERT ON `ms_dealers` FOR EACH ROW 
            BEGIN
                DECLARE v_screen_id VARCHAR(50);
                DECLARE v_new_response LONGTEXT;
                
                -- Get Screen ID (Ensure it doesn't break if '-' is missing)
                SET v_screen_id = IFNULL(SUBSTRING_INDEX(NEW.dealer_id, '-', 1), '');
                
                -- Generate New Response (Safely formatted JSON string)
                SET v_new_response = CONCAT(
                    '{', CHAR(10),
                    '  \"dealer_code\": \"', IFNULL(NEW.dealer_code, ''), '\",', CHAR(10),
                    '  \"dealer_name\": \"', IFNULL(NEW.dealer_name, ''), '\",', CHAR(10),
                    '  \"city\": \"', IFNULL(NEW.city, ''), '\",', CHAR(10),
                    '  \"created_date\": \"', IFNULL(NEW.created_date, ''), '\",', CHAR(10),
                    '  \"created_by\": \"', IFNULL(NEW.created_by, ''), '\",', CHAR(10),
                    '  \"unique_id\": \"', IFNULL(NEW.unique_id, ''), '\",', CHAR(10),
                    '  \"is_active\": \"', IFNULL(NEW.is_active, 0), '\"', CHAR(10),
                    '}'
                );
                
                -- Insert Into Audit Trail Log
                INSERT INTO ad_trail_log_master_data (
                    user_id,
                    screen_id,
                    old_response,
                    new_response,
                    execution_type,
                    executed_at
                )
                VALUES (
                    NEW.created_by,
                    v_screen_id,
                    NULL,
                    v_new_response,
                    'INSERT',
                    NEW.created_date
                );
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `tg_ms_dealers_AFTER_INSERT`");
    }
};
