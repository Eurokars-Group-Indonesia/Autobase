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
            CREATE TRIGGER `tg_ms_brand_AFTER_UPDATE` AFTER UPDATE ON `ms_brand` FOR EACH ROW 
            BEGIN
                DECLARE v_screen_id VARCHAR(50);
                DECLARE v_old_response LONGTEXT;
                DECLARE v_new_response LONGTEXT;
                
                -- Get Screen ID Safely
                SET v_screen_id = IFNULL(SUBSTRING_INDEX(OLD.brand_id, '-', 1), '');
                
                -- Generate Old Response using CONCAT
                SET v_old_response = CONCAT(
                    '{', CHAR(10),
                    '  \"brand_name\": \"', IFNULL(OLD.brand_name, ''), '\",', CHAR(10),
                    '  \"brand_code\": \"', IFNULL(OLD.brand_code, ''), '\",', CHAR(10),
                    '  \"brand_group\": \"', IFNULL(OLD.brand_group, ''), '\",', CHAR(10),
                    '  \"country_origin\": \"', IFNULL(OLD.country_origin, ''), '\",', CHAR(10),
                    '  \"created_date\": \"', IFNULL(OLD.created_date, ''), '\",', CHAR(10),
                    '  \"created_by\": \"', IFNULL(OLD.created_by, ''), '\",', CHAR(10),
                    '  \"updated_date\": \"', IFNULL(OLD.updated_date, ''), '\",', CHAR(10),
                    '  \"updated_by\": \"', IFNULL(OLD.updated_by, ''), '\",', CHAR(10),
                    '  \"unique_id\": \"', IFNULL(OLD.unique_id, ''), '\",', CHAR(10),
                    '  \"is_active\": \"', IFNULL(OLD.is_active, 0), '\"', CHAR(10),
                    '}'
                );
                
                -- Generate New Response using CONCAT
                SET v_new_response = CONCAT(
                    '{', CHAR(10),
                    '  \"brand_name\": \"', IFNULL(NEW.brand_name, ''), '\",', CHAR(10),
                    '  \"brand_code\": \"', IFNULL(NEW.brand_code, ''), '\",', CHAR(10),
                    '  \"brand_group\": \"', IFNULL(NEW.brand_group, ''), '\",', CHAR(10),
                    '  \"country_origin\": \"', IFNULL(NEW.country_origin, ''), '\",', CHAR(10),
                    '  \"created_date\": \"', IFNULL(NEW.created_date, ''), '\",', CHAR(10),
                    '  \"created_by\": \"', IFNULL(NEW.created_by, ''), '\",', CHAR(10),
                    '  \"updated_date\": \"', IFNULL(NEW.updated_date, ''), '\",', CHAR(10),
                    '  \"updated_by\": \"', IFNULL(NEW.updated_by, ''), '\",', CHAR(10),
                    '  \"unique_id\": \"', IFNULL(NEW.unique_id, ''), '\",', CHAR(10),
                    '  \"is_active\": \"', IFNULL(NEW.is_active, 0), '\"', CHAR(10),
                    '}'
                );
                
                -- Determine Execution Type
                IF NEW.is_active = '0' THEN
                    INSERT INTO ad_trail_log_master_data (
                        user_id, 
                        screen_id, 
                        old_response, 
                        new_response, 
                        execution_type, 
                        executed_at
                    ) 
                    VALUES (
                        NEW.updated_by, 
                        v_screen_id, 
                        v_old_response, 
                        v_new_response, 
                        'DELETE', 
                        NEW.updated_date
                    );
                ELSE
                    INSERT INTO ad_trail_log_master_data (
                        user_id, 
                        screen_id, 
                        old_response, 
                        new_response, 
                        execution_type, 
                        executed_at
                    ) 
                    VALUES (
                        NEW.updated_by, 
                        v_screen_id, 
                        v_old_response, 
                        v_new_response, 
                        'UPDATE', 
                        NEW.updated_date
                    );
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `tg_ms_brand_AFTER_UPDATE`");
    }
};
