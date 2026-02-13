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
            DROP PROCEDURE IF EXISTS sp_update_ms_permission;

            CREATE PROCEDURE `sp_update_ms_permission`(
                IN p_user_id VARCHAR(50),
                IN p_permission_code VARCHAR(100),
                IN p_permission_name VARCHAR(150),
                IN p_unique_id VARCHAR(50)
            )
            proc_label: BEGIN
                /* Changelog :
                * v1 : [2026-01-29] : Create Store Procedure Update ms_permissions
                */
                
                /* Flow :
                * 1. Check if user exists, if not found then return 404
                * 2. Check if Permission is exists, if not found then return 404
                * 3. Check for duplicates data by checking permission_code or permission_name, if found then return 409
                * 4. Update data
                */
                
                DECLARE v_duplicate_count INT; -- menampung variable untuk check duplicate data
                DECLARE v_return_code INT DEFAULT 200; -- menampung variable untuk return code
                DECLARE v_return_message VARCHAR(255) DEFAULT 'Success'; -- menampung variable untuk return message
                DECLARE v_exists_user INT; -- menampung variable untuk check user
                DECLARE v_exists INT; -- menampung variable untuk check data Permission
                
                -- Check if user exists
                SELECT COUNT(user_id) INTO v_exists_user
                FROM ms_users
                WHERE user_id = p_user_id
                AND is_active = '1';
                
                -- if user not found then return 404
                IF v_exists_user = 0 THEN
                    SET v_return_code = 404;
                    SET v_return_message = 'User not found';
                    
                    -- Return error code and message
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Check if Permission is exists
                SELECT COUNT(permission_id) INTO v_exists
                FROM ms_permissions
                WHERE unique_id = p_unique_id
                AND is_active = '1';
                
                -- if Permission not found then return 404
                IF v_exists = 0 THEN
                    SET v_return_code = 404;
                    SET v_return_message = 'Permission not found';
                    
                    -- Return error code and message
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Check for duplicates
                SELECT COUNT(permission_id) INTO v_duplicate_count
                FROM ms_permissions
                WHERE (permission_code = p_permission_code
                    OR permission_name = p_permission_name)
                AND unique_id <> p_unique_id
                AND is_active = '1';
                
                IF v_duplicate_count >= 1 THEN
                    SET v_return_code = 409;
                    SET v_return_message = 'Permission already Created';
                    
                    -- Return error code and message
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Update data
                UPDATE ms_permissions
                SET permission_code = p_permission_code,
                    permission_name = p_permission_name,
                    updated_by = p_user_id,
                    updated_date = NOW()
                WHERE unique_id = p_unique_id
                AND is_active = '1';
                
                -- Return success code, message, and data
                SELECT 
                    v_return_code AS return_code, 
                    v_return_message AS return_message,
                    permission_id,
                    permission_code,
                    permission_name,
                    updated_by,
                    updated_date,
                    unique_id
                FROM ms_permissions 
                WHERE unique_id = p_unique_id
                AND is_active = '1'
                LIMIT 1;
            END proc_label
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS `sp_update_ms_permission`");
    }
};
