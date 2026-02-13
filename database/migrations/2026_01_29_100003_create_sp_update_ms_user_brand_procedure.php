<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_update_ms_user_brand;

            CREATE PROCEDURE `sp_update_ms_user_brand`(
                IN p_user_id VARCHAR(50),
                IN p_brand_id VARCHAR(50),
                IN p_updated_by VARCHAR(50),
                IN p_unique_id VARCHAR(50)
            )
            proc_label: BEGIN
                /* Changelog :
                * v1 : [2026-01-29] : Create Store Procedure Update ms_user_brand
                */
                
                /* Flow :
                * 1. Check if updated_by user exists, if not found then return 404
                * 2. Check if user exists, if not found then return 404
                * 3. Check if brand exists, if not found then return 404
                * 4. Check if user brand exists, if not found then return 404
                * 5. Check for duplicates data, if found then return 409
                * 6. Update data
                */
                
                DECLARE v_duplicate_count INT;
                DECLARE v_return_code INT DEFAULT 200;
                DECLARE v_return_message VARCHAR(255) DEFAULT 'Success';
                DECLARE v_exists_updated_by INT;
                DECLARE v_exists_user INT;
                DECLARE v_exists_brand INT;
                DECLARE v_exists INT;
                
                -- Check if updated_by user exists
                SELECT COUNT(user_id) INTO v_exists_updated_by
                FROM ms_users
                WHERE user_id = p_updated_by
                AND is_active = '1';
                
                IF v_exists_updated_by = 0 THEN
                    SET v_return_code = 404;
                    SET v_return_message = 'Updated by user not found';
                    
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
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
                
                -- Check if brand exists
                SELECT COUNT(brand_id) INTO v_exists_brand
                FROM ms_brand
                WHERE brand_id = p_brand_id
                AND is_active = '1';
                
                IF v_exists_brand = 0 THEN
                    SET v_return_code = 404;
                    SET v_return_message = 'Brand not found';
                    
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Check if user brand exists
                SELECT COUNT(user_brand_id) INTO v_exists
                FROM ms_user_brand
                WHERE unique_id = p_unique_id
                AND is_active = '1';
                
                IF v_exists = 0 THEN
                    SET v_return_code = 404;
                    SET v_return_message = 'User brand not found';
                    
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Check for duplicates
                SELECT COUNT(user_brand_id) INTO v_duplicate_count
                FROM ms_user_brand
                WHERE user_id = p_user_id
                AND brand_id = p_brand_id
                AND unique_id <> p_unique_id
                AND is_active = '1';
                
                IF v_duplicate_count >= 1 THEN
                    SET v_return_code = 409;
                    SET v_return_message = 'User brand already assigned';
                    
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Update data
                UPDATE ms_user_brand
                SET user_id = p_user_id,
                    brand_id = p_brand_id,
                    updated_by = p_updated_by,
                    updated_date = NOW()
                WHERE unique_id = p_unique_id
                AND is_active = '1';
                
                -- Return success code, message, and data
                SELECT 
                    v_return_code AS return_code, 
                    v_return_message AS return_message,
                    user_brand_id,
                    user_id,
                    brand_id,
                    updated_by,
                    updated_date,
                    unique_id,
                    is_active
                FROM ms_user_brand 
                WHERE unique_id = p_unique_id
                AND is_active = '1'
                LIMIT 1;
            END proc_label
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS `sp_update_ms_user_brand`");
    }
};
