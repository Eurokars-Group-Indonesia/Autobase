<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_add_ms_role_menu;

            CREATE PROCEDURE `sp_add_ms_role_menu`(
                IN p_role_id VARCHAR(50),
                IN p_menu_id VARCHAR(50),
                IN p_user_id VARCHAR(50),
                IN p_unique_id VARCHAR(50)
            )
            proc_label: BEGIN
                /* Changelog :
                * v1 : [2026-01-30] : Create Store Procedure Add ms_role_menus
                */
                
                /* Flow :
                * 1. Check if user exists, if not found then return 404
                * 2. Check if role exists, if not found then return 404
                * 3. Check if menu exists, if not found then return 404
                * 4. Check if role menu already exists, if exists then return 409
                * 5. Generate new role_menu_id
                * 6. Insert new role menu
                */
                
                DECLARE v_return_code INT DEFAULT 200;
                DECLARE v_return_message VARCHAR(255) DEFAULT 'Success';
                DECLARE v_exists_user INT;
                DECLARE v_exists_role INT;
                DECLARE v_exists_menu INT;
                DECLARE v_exists INT;
                DECLARE v_generated_id VARCHAR(50);
                DECLARE v_screen_id VARCHAR(25) DEFAULT 'MRM01';
                
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
                
                -- Check if menu exists
                SELECT COUNT(menu_id) INTO v_exists_menu
                FROM ms_menus
                WHERE menu_id = p_menu_id
                AND is_active = '1';
                
                IF v_exists_menu = 0 THEN
                    SET v_return_code = 404;
                    SET v_return_message = 'Menu not found';
                    
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Check if role menu already exists
                SELECT COUNT(role_menu_id) INTO v_exists
                FROM ms_role_menus
                WHERE role_id = p_role_id
                AND menu_id = p_menu_id;
                
                IF v_exists > 0 THEN
                    SET v_return_code = 409;
                    SET v_return_message = 'Role menu already exists';
                    
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Generate new role_menu_id
                SELECT fn_gen_number(v_screen_id) INTO v_generated_id;
                
                -- Insert new role menu
                INSERT INTO ms_role_menus (
                    role_menu_id,
                    role_id,
                    menu_id,
                    created_by,
                    created_date,
                    unique_id,
                    is_active
                ) VALUES (
                    v_generated_id,
                    p_role_id,
                    p_menu_id,
                    p_user_id,
                    NOW(),
                    p_unique_id,
                    '1'
                );
                
                -- Return success code and message
                SELECT 
                    v_return_code AS return_code, 
                    v_return_message AS return_message,
                    role_menu_id,
                    role_id,
                    menu_id,
                    is_active,
                    created_by,
                    created_date
                FROM ms_role_menus 
                WHERE role_menu_id = v_generated_id
                LIMIT 1;
            END proc_label
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS `sp_add_ms_role_menu`");
    }
};
