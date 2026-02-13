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

            DROP PROCEDURE IF EXISTS sp_add_ms_menu;

            CREATE PROCEDURE `sp_add_ms_menu`(
                IN `p_user_id` VARCHAR(50),
                IN `p_menu_code` VARCHAR(50),
                IN `p_menu_name` VARCHAR(100),
                IN `p_menu_url` VARCHAR(255),
                IN `p_menu_icon` VARCHAR(50),
                IN `p_parent_id` VARCHAR(50),
                IN `p_menu_order` INT UNSIGNED,
                IN `p_unique_id` VARCHAR(50)
            )
            proc_label: BEGIN
                /* Changelog :
                * v1 : [2026-01-27] (Bernand Dayamuntari Hermawan) : Create Store Procedure Add ms_menus
                */
                
                /* Flow :
                * 1. Check if user exists, if not found then return 404
                * 2. Check for duplicates data by checking menu_code, if found then return 409
                * 3. Generate ID using an existing function
                * 4. Insert data
                */
                
                DECLARE v_duplicate_count INT; -- menampung variable untuk check duplicate data
                DECLARE v_generated_id VARCHAR(50); -- menampung variable untuk generate id
                DECLARE v_return_code INT DEFAULT 200; -- menampung variable untuk return code
                DECLARE v_return_message VARCHAR(255) DEFAULT 'Success'; -- menampung variable untuk return message
                DECLARE v_exists_user INT; -- menampung variable untuk check user
                DECLARE v_screen_id VARCHAR(25) DEFAULT 'MMN01';
                
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
                
                -- Check for duplicates
                SELECT COUNT(menu_id) INTO v_duplicate_count
                FROM ms_menus
                WHERE menu_code = p_menu_code
                AND is_active = '1';
                
                IF v_duplicate_count >= 1 THEN
                    SET v_return_code = 409;
                    SET v_return_message = 'Menu already Created';
                    
                    -- Return error code and message
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Generate ID using an existing function
                SELECT fn_gen_number(v_screen_id) INTO v_generated_id;
                
                -- Insert new data
                INSERT INTO ms_menus (
                    menu_id, 
                    menu_code, 
                    menu_name, 
                    menu_url, 
                    menu_icon, 
                    parent_id, 
                    menu_order, 
                    created_by, 
                    created_date, 
                    unique_id
                ) VALUES (
                    v_generated_id, 
                    p_menu_code, 
                    p_menu_name, 
                    p_menu_url, 
                    p_menu_icon, 
                    p_parent_id, 
                    p_menu_order, 
                    p_user_id, 
                    NOW(), 
                    p_unique_id
                );
                
                -- Return success code, message, and data
                SELECT 
                    v_return_code AS return_code, 
                    v_return_message AS return_message,
                    menu_id,
                    menu_code,
                    menu_name,
                    menu_url,
                    menu_icon,
                    parent_id,
                    menu_order,
                    created_by,
                    created_date,
                    unique_id
                FROM ms_menus 
                WHERE menu_id = v_generated_id
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
        DB::unprepared("DROP PROCEDURE IF EXISTS `sp_add_ms_menu`");
    }
};
