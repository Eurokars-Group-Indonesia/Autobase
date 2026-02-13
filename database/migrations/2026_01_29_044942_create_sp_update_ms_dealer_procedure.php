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

            DROP PROCEDURE IF EXISTS sp_update_ms_dealer;

            CREATE PROCEDURE `sp_update_ms_dealer`(
                IN `p_user_id` VARCHAR(50),
                IN `p_dealer_name` VARCHAR(150),
                IN `p_dealer_code` VARCHAR(50),
                IN `p_city` VARCHAR(100),
                IN `p_unique_id` VARCHAR(50)
            )
            proc_label: BEGIN
                /* Changelog :
                * v1 : [2026-01-27] (Bernand Dayamuntari Hermawan) : Create Store Procedure Update ms_dealers
                */
                
                /* Flow :
                * 1. Check if user exists, if not found then return 404
                * 2. Check if Dealer is exists, if not found then return 404
                * 3. Check for duplicates data by checking dealer_name or dealer_code, if found then return 409
                * 4. Update data
                */
                
                DECLARE v_duplicate_count INT; -- menampung variable untuk check duplicate data
                DECLARE v_return_code INT DEFAULT 200; -- menampung variable untuk return code
                DECLARE v_return_message VARCHAR(255) DEFAULT 'Success'; -- menampung variable untuk return message
                DECLARE v_exists_user INT; -- menampung variable untuk check user
                DECLARE v_exists INT; -- menampung variable untuk check data Dealer
                
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
                
                -- Check if Dealer is exists
                SELECT COUNT(dealer_id) INTO v_exists
                FROM ms_dealers
                WHERE unique_id = p_unique_id
                AND is_active = '1';
                
                -- if Dealer not found then return 404
                IF v_exists = 0 THEN
                    SET v_return_code = 404;
                    SET v_return_message = 'Dealer not found';
                    
                    -- Return error code and message
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Check for duplicates
                SELECT COUNT(dealer_id) INTO v_duplicate_count
                FROM ms_dealers
                WHERE (dealer_name = p_dealer_name
                    OR dealer_code = p_dealer_code)
                AND unique_id <> p_unique_id
                AND is_active = '1';
                
                IF v_duplicate_count >= 1 THEN
                    SET v_return_code = 409;
                    SET v_return_message = 'Dealer already Created';
                    
                    -- Return error code and message
                    SELECT v_return_code AS return_code, v_return_message AS return_message, NULL AS data;
                    LEAVE proc_label;
                END IF;
                
                -- Update data
                UPDATE ms_dealers
                SET dealer_name = p_dealer_name,
                    dealer_code = p_dealer_code,
                    city = p_city,
                    updated_by = p_user_id,
                    updated_date = NOW()
                WHERE unique_id = p_unique_id
                AND is_active = '1';
                
                -- Return success code, message, and data
                SELECT 
                    v_return_code AS return_code, 
                    v_return_message AS return_message,
                    dealer_id,
                    dealer_name,
                    dealer_code,
                    city,
                    updated_by,
                    updated_date,
                    unique_id
                FROM ms_dealers 
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
        DB::unprepared("DROP PROCEDURE IF EXISTS `sp_update_ms_dealer`");
    }
};
