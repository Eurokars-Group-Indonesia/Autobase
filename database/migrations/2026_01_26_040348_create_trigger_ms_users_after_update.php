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
            CREATE TRIGGER `tg_ms_users_AFTER_UPDATE` AFTER UPDATE ON `ms_users` FOR EACH ROW 
            BEGIN
                DECLARE v_screen_id VARCHAR(50);
                DECLARE v_old_response LONGTEXT;
                DECLARE v_new_response LONGTEXT;
                
                -- Get Screen ID Safely
                SET v_screen_id = IFNULL(SUBSTRING_INDEX(OLD.user_id, '-', 1), '');
                
                -- Generate Old Response using CONCAT
                -- Note: Password is excluded for security reasons
                SET v_old_response = CONCAT(
                    '{', CHAR(10),
                    '  \"name\": \"', IFNULL(OLD.name, ''), '\",', CHAR(10),
                    '  \"email\": \"', IFNULL(OLD.email, ''), '\",', CHAR(10),
                    '  \"full_name\": \"', IFNULL(OLD.full_name, ''), '\",', CHAR(10),
                    '  \"phone\": \"', IFNULL(OLD.phone, ''), '\",', CHAR(10),
                    '  \"dealer_id\": \"', IFNULL(OLD.dealer_id, ''), '\",', CHAR(10),
                    '  \"created_date\": \"', IFNULL(OLD.created_date, ''), '\",', CHAR(10),
                    '  \"created_by\": \"', IFNULL(OLD.created_by, ''), '\",', CHAR(10),
                    '  \"updated_date\": \"', IFNULL(OLD.updated_date, ''), '\",', CHAR(10),
                    '  \"updated_by\": \"', IFNULL(OLD.updated_by, ''), '\",', CHAR(10),
                    '  \"last_login\": \"', IFNULL(OLD.last_login, ''), '\",', CHAR(10),
                    '  \"unique_id\": \"', IFNULL(OLD.unique_id, ''), '\",', CHAR(10),
                    '  \"is_active\": \"', IFNULL(OLD.is_active, 0), '\"', CHAR(10),
                    '}'
                );
                
                -- Generate New Response using CONCAT
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
                    '  \"updated_date\": \"', IFNULL(NEW.updated_date, ''), '\",', CHAR(10),
                    '  \"updated_by\": \"', IFNULL(NEW.updated_by, ''), '\",', CHAR(10),
                    '  \"last_login\": \"', IFNULL(NEW.last_login, ''), '\",', CHAR(10),
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
        DB::unprepared("DROP TRIGGER IF EXISTS `tg_ms_users_AFTER_UPDATE`");
    }
};
