<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_update_ms_role;

            CREATE PROCEDURE `sp_update_ms_role`(
                IN p_role_code VARCHAR(10),
                IN p_role_name VARCHAR(50),
                IN p_role_description VARCHAR(200),
                IN p_user_id VARCHAR(50),
                IN p_unique_id VARCHAR(50)
            )
            proc_label: BEGIN
                /* Changelog :
                * v1 : [2026-01-29] : Create Store Procedure Update ms_role
                */
                
                /* Flow :
                * 1. Check if user exists, if not found then return 404
                * 2. Check if Role is exists, if not found then return 404
                * 3. Check for duplicates data by checking role_code or role_name, if found then return 409
                * 4. Update data
                */
                
                DECLARE v_duplicate_count INT;
                DECLARE v_return_code INT DEFAULT 200;
                DECLARE v_return_message VARCHAR(255) DEFAULT 'Success';
                DECLARE v_exists_user INT;
                DECLARE v_exists INT;
                
                -- Check if user exists
                SELECT COUNT(user_id) INTO v_exists_user
                FROM ms_users
                WHERE user_id = p_user_id
                AND is_active = '1';
                
                IF v_exists_user = 0 THEN
                    SET v_return_code = 404;
                    SET v_return_message = 'User not found';
                    
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Check if Role is exists
                SELECT COUNT(role_id) INTO v_exists
                FROM ms_role
                WHERE unique_id = p_unique_id
                AND is_active = '1';
                
                IF v_exists = 0 THEN
                    SET v_return_code = 404;
                    SET v_return_message = 'Role not found';
                    
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Check for duplicates
                SELECT COUNT(role_id) INTO v_duplicate_count
                FROM ms_role
                WHERE (role_code = p_role_code
                    OR role_name = p_role_name)
                AND unique_id <> p_unique_id
                AND is_active = '1';
                
                IF v_duplicate_count >= 1 THEN
                    SET v_return_code = 409;
                    SET v_return_message = 'Role already Created';
                    
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Update data
                UPDATE ms_role
                SET role_code = p_role_code,
                    role_name = p_role_name,
                    role_description = p_role_description,
                    updated_by = p_user_id,
                    updated_date = NOW()
                WHERE unique_id = p_unique_id
                AND is_active = '1';
                
                -- Return success code, message, and data
                SELECT 
                    v_return_code AS return_code, 
                    v_return_message AS return_message,
                    role_id,
                    role_code,
                    role_name,
                    role_description,
                    updated_by,
                    updated_date,
                    unique_id
                FROM ms_role 
                WHERE unique_id = p_unique_id
                AND is_active = '1'
                LIMIT 1;
            END proc_label
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS `sp_update_ms_role`");
    }
};
