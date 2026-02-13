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
            CREATE TRIGGER `tg_ms_users_AFTER_INSERT` AFTER INSERT ON `ms_users` FOR EACH ROW 
            BEGIN
                DECLARE v_screen_id VARCHAR(50);
                DECLARE v_new_response LONGTEXT;
                
                -- Get Screen ID (Ensure it doesn't break if '-' is missing)
                SET v_screen_id = IFNULL(SUBSTRING_INDEX(NEW.user_id, '-', 1), '');
                
                -- Generate New Response (Safely formatted JSON string)
                -- Note: Password is excluded for security reasons
                SET v_new_response = CONCAT(
                    '{', CHAR(10),
                    '  \"name\": \"', IFNULL(NEW.name, ''), '\",', CHAR(10),
                    '  \"email\": \"', IFNULL(NEW.email, ''), '\",', CHAR(10),
                    '  \"full_name\": \"', IFNULL(NEW.full_name, ''), '\",', CHAR(10),
                    '  \"phone\": \"', IFNULL(NEW.phone, ''), '\",', CHAR(10),
                    '  \"dealer_id\": \"', IFNULL(NEW.dealer_id, ''), '\",', CHAR(10),
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
                    IFNULL(NEW.created_by, NEW.user_id),
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
        DB::unprepared("DROP TRIGGER IF EXISTS `tg_ms_users_AFTER_INSERT`");
    }
};
