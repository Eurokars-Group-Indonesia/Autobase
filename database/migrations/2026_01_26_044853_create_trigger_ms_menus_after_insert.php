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
            CREATE TRIGGER `tg_ms_menus_AFTER_INSERT` AFTER INSERT ON `ms_menus` FOR EACH ROW 
            BEGIN
                DECLARE v_screen_id VARCHAR(50);
                DECLARE v_new_response LONGTEXT;
                
                -- Get Screen ID (Ensure it doesn't break if '-' is missing)
                SET v_screen_id = IFNULL(SUBSTRING_INDEX(NEW.menu_id, '-', 1), '');
                
                -- Generate New Response (Safely formatted JSON string)
                SET v_new_response = CONCAT(
                    '{', CHAR(10),
                    '  \"menu_code\": \"', IFNULL(NEW.menu_code, ''), '\",', CHAR(10),
                    '  \"menu_name\": \"', IFNULL(NEW.menu_name, ''), '\",', CHAR(10),
                    '  \"menu_url\": \"', IFNULL(NEW.menu_url, ''), '\",', CHAR(10),
                    '  \"menu_icon\": \"', IFNULL(NEW.menu_icon, ''), '\",', CHAR(10),
                    '  \"parent_id\": \"', IFNULL(NEW.parent_id, ''), '\",', CHAR(10),
                    '  \"menu_order\": \"', IFNULL(NEW.menu_order, 0), '\",', CHAR(10),
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
        DB::unprepared("DROP TRIGGER IF EXISTS `tg_ms_menus_AFTER_INSERT`");
    }
};
