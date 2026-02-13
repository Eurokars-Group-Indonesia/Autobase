<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_add_ms_role_permission;

            CREATE PROCEDURE `sp_add_ms_role_permission`(
                IN p_role_id VARCHAR(50),
                IN p_permission_id VARCHAR(50),
                IN p_user_id VARCHAR(50),
                IN p_unique_id VARCHAR(50)
            )
            proc_label: BEGIN
                /* Changelog :
                * v1 : [2026-01-30] : Create Store Procedure Add ms_role_permissions
                */
                
                /* Flow :
                * 1. Check if user exists, if not found then return 404
                * 2. Check if role exists, if not found then return 404
                * 3. Check if permission exists, if not found then return 404
                * 4. Check if role permission already exists, if exists then return 409
                * 5. Generate new role_permission_id
                * 6. Insert new role permission
                */
                
                DECLARE v_return_code INT DEFAULT 200;
                DECLARE v_return_message VARCHAR(255) DEFAULT 'Success';
                DECLARE v_exists_user INT;
                DECLARE v_exists_role INT;
                DECLARE v_exists_permission INT;
                DECLARE v_exists INT;
                DECLARE v_generated_id VARCHAR(50);
                DECLARE v_screen_id VARCHAR(25) DEFAULT 'MRP01';
                
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
                
                -- Check if role exists
                SELECT COUNT(role_id) INTO v_exists_role
                FROM ms_role
                WHERE role_id = p_role_id
                AND is_active = '1';
                
                IF v_exists_role = 0 THEN
                    SET v_return_code = 404;
                    SET v_return_message = 'Role not found';
                    
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Check if permission exists
                SELECT COUNT(permission_id) INTO v_exists_permission
                FROM ms_permissions
                WHERE permission_id = p_permission_id
                AND is_active = '1';
                
                IF v_exists_permission = 0 THEN
                    SET v_return_code = 404;
                    SET v_return_message = 'Permission not found';
                    
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Check if role permission already exists
                SELECT COUNT(role_permission_id) INTO v_exists
                FROM ms_role_permissions
                WHERE role_id = p_role_id
                AND permission_id = p_permission_id;
                
                IF v_exists > 0 THEN
                    SET v_return_code = 409;
                    SET v_return_message = 'Role permission already exists';
                    
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Generate new role_permission_id
                SELECT fn_gen_number(v_screen_id) INTO v_generated_id;
                
                -- Insert new role permission
                INSERT INTO ms_role_permissions (
                    role_permission_id,
                    role_id,
                    permission_id,
                    created_by,
                    created_date,
                    unique_id,
                    is_active
                ) VALUES (
                    v_generated_id,
                    p_role_id,
                    p_permission_id,
                    p_user_id,
                    NOW(),
                    p_unique_id,
                    '1'
                );
                
                -- Return success code and message
                SELECT 
                    v_return_code AS return_code, 
                    v_return_message AS return_message,
                    role_permission_id,
                    role_id,
                    permission_id,
                    is_active,
                    created_by,
                    created_date
                FROM ms_role_permissions 
                WHERE role_permission_id = v_generated_id
                LIMIT 1;
            END proc_label
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS `sp_add_ms_role_permission`");
    }
};
