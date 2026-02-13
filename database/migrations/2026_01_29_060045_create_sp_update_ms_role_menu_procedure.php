<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_update_ms_role_menu;

            CREATE PROCEDURE `sp_update_ms_role_menu`(
                IN p_role_id VARCHAR(50),
                IN p_menu_id VARCHAR(50),
                IN p_is_active ENUM('0', '1'),
                IN p_user_id VARCHAR(50),
                IN p_unique_id VARCHAR(50)
            )
            proc_label: BEGIN
                /* Changelog :
                * v1 : [2026-01-29] : Create Store Procedure Update ms_role_menus
                */
                
                /* Flow :
                * 1. Check if user exists, if not found then return 404
                * 2. Check if Role Menu exists, if not found then insert new
                * 3. Update is_active status
                */
                
                DECLARE v_return_code INT DEFAULT 200;
                DECLARE v_return_message VARCHAR(255) DEFAULT 'Success';
                DECLARE v_exists_user INT;
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
                
                -- Check if Role Menu exists
                SELECT COUNT(role_menu_id) INTO v_exists
                FROM ms_role_menus
                WHERE role_id = p_role_id
                AND menu_id = p_menu_id;
                
                IF v_exists = 0 THEN
                    -- Insert new if not exists
                    SELECT fn_gen_number(v_screen_id) INTO v_generated_id;
                    
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
                        p_is_active
                    );
                ELSE
                    -- Update existing
                    UPDATE ms_role_menus
                    SET is_active = p_is_active,
                        updated_by = p_user_id,
                        updated_date = NOW()
                    WHERE role_id = p_role_id
                    AND menu_id = p_menu_id;
                END IF;
                
                -- Return success code and message
                SELECT 
                    v_return_code AS return_code, 
                    v_return_message AS return_message,
                    role_menu_id,
                    role_id,
                    menu_id,
                    is_active,
                    updated_by,
                    updated_date
                FROM ms_role_menus 
                WHERE role_id = p_role_id
                AND menu_id = p_menu_id
                LIMIT 1;
            END proc_label
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS `sp_update_ms_role_menu`");
    }
};
