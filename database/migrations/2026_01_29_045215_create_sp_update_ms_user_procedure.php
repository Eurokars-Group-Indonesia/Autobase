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
            DROP PROCEDURE IF EXISTS sp_update_ms_user;

            CREATE PROCEDURE `sp_update_ms_user`(
                IN p_updated_by VARCHAR(50),
                IN p_dealer_id VARCHAR(50),
                IN p_name VARCHAR(150),
                IN p_email VARCHAR(150),
                IN p_full_name VARCHAR(150),
                IN p_password VARCHAR(255),
                IN p_phone VARCHAR(20),
                IN p_unique_id VARCHAR(50)
            )
            proc_label: BEGIN
                /* Changelog :
                * v1 : [2026-01-27] : Create Store Procedure Update ms_users
                */
                
                /* Flow :
                * 1. Check if updated_by user exists, if not found then return 404
                * 2. Check if User to be updated exists, if not found then return 404
                * 3. Check if dealer exists (if provided), if not found then return 404
                * 4. Check for duplicates data by checking email, if found then return 409
                * 5. Update data
                */
                
                DECLARE v_duplicate_count INT; -- menampung variable untuk check duplicate data
                DECLARE v_return_code INT DEFAULT 200; -- menampung variable untuk return code
                DECLARE v_return_message VARCHAR(255) DEFAULT 'Success'; -- menampung variable untuk return message
                DECLARE v_exists_user INT; -- menampung variable untuk check user
                DECLARE v_exists INT; -- menampung variable untuk check data User
                DECLARE v_exists_dealer INT; -- menampung variable untuk check dealer
                
                -- Check if updated_by user exists
                SELECT COUNT(user_id) INTO v_exists_user
                FROM ms_users
                WHERE user_id = p_updated_by
                AND is_active = '1';
                
                -- if user not found then return 404
                IF v_exists_user = 0 THEN
                    SET v_return_code = 404;
                    SET v_return_message = 'Updater user not found';
                    
                    -- Return error code and message
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Check if User to be updated exists
                SELECT COUNT(user_id) INTO v_exists
                FROM ms_users
                WHERE unique_id = p_unique_id
                AND is_active = '1';
                
                -- if User not found then return 404
                IF v_exists = 0 THEN
                    SET v_return_code = 404;
                    SET v_return_message = 'User not found';
                    
                    -- Return error code and message
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Check if dealer exists (if provided)
                IF p_dealer_id IS NOT NULL THEN
                    SELECT COUNT(dealer_id) INTO v_exists_dealer
                    FROM ms_dealers
                    WHERE dealer_id = p_dealer_id
                    AND is_active = '1';
                    
                    IF v_exists_dealer = 0 THEN
                        SET v_return_code = 404;
                        SET v_return_message = 'Dealer not found';
                        
                        -- Return error code and message
                        SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                        LEAVE proc_label;
                    END IF;
                END IF;
                
                -- Check for duplicates by email
                SELECT COUNT(user_id) INTO v_duplicate_count
                FROM ms_users
                WHERE email = p_email
                AND unique_id <> p_unique_id
                AND is_active = '1';
                
                IF v_duplicate_count >= 1 THEN
                    SET v_return_code = 409;
                    SET v_return_message = 'User with this email already exists';
                    
                    -- Return error code and message
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Update data
                -- Only update password if provided (not empty)
                IF p_password IS NOT NULL AND p_password != '' THEN
                    UPDATE ms_users
                    SET dealer_id = p_dealer_id,
                        name = p_name,
                        email = p_email,
                        full_name = p_full_name,
                        password = p_password,
                        phone = p_phone,
                        updated_by = p_updated_by,
                        updated_date = NOW()
                    WHERE unique_id = p_unique_id
                    AND is_active = '1';
                ELSE
                    UPDATE ms_users
                    SET dealer_id = p_dealer_id,
                        name = p_name,
                        email = p_email,
                        full_name = p_full_name,
                        phone = p_phone,
                        updated_by = p_updated_by,
                        updated_date = NOW()
                    WHERE unique_id = p_unique_id
                    AND is_active = '1';
                END IF;
                
                -- Return success code, message, and data
                SELECT 
                    v_return_code AS return_code, 
                    v_return_message AS return_message,
                    user_id,
                    dealer_id,
                    name,
                    email,
                    full_name,
                    phone,
                    updated_by,
                    updated_date,
                    unique_id
                FROM ms_users 
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
        DB::unprepared("DROP PROCEDURE IF EXISTS `sp_update_ms_user`");
    }
};
