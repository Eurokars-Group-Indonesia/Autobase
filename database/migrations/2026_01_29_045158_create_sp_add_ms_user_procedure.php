<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_add_ms_user;

            CREATE PROCEDURE sp_add_ms_user(
                IN p_created_by VARCHAR(50),
                IN p_dealer_id VARCHAR(50),
                IN p_name VARCHAR(150),
                IN p_email VARCHAR(150),
                IN p_full_name VARCHAR(150),
                IN p_password VARCHAR(255),
                IN p_phone VARCHAR(20),
                IN p_unique_id VARCHAR(50)
            )
            BEGIN
                -- =====================
                -- Variable Declaration
                -- =====================
                DECLARE v_duplicate_count INT DEFAULT 0;
                DECLARE v_generated_id VARCHAR(50);
                DECLARE v_return_code INT DEFAULT 200;
                DECLARE v_return_message VARCHAR(255) DEFAULT 'Success';
                DECLARE v_exists_user INT DEFAULT 0;
                DECLARE v_exists_dealer INT DEFAULT 0;
                DECLARE v_screen_id VARCHAR(25) DEFAULT 'MUS01';

                proc_label: BEGIN
                    -- =====================
                    -- Check created_by user exists
                    -- =====================
                    SELECT COUNT(user_id)
                    INTO v_exists_user
                    FROM ms_users
                    WHERE user_id = p_created_by
                    AND is_active = '1';

                    IF v_exists_user = 0 THEN
                        SET v_return_code = 404;
                        SET v_return_message = 'Creator user not found';

                        SELECT 
                            v_return_code AS return_code,
                            v_return_message AS return_message,
                            NULL AS data;
                        LEAVE proc_label;
                    END IF;

                    -- =====================
                    -- Check dealer exists (if provided)
                    -- =====================
                    IF p_dealer_id IS NOT NULL THEN
                        SELECT COUNT(dealer_id)
                        INTO v_exists_dealer
                        FROM ms_dealers
                        WHERE dealer_id = p_dealer_id
                        AND is_active = '1';

                        IF v_exists_dealer = 0 THEN
                            SET v_return_code = 404;
                            SET v_return_message = 'Dealer not found';

                            SELECT 
                                v_return_code AS return_code,
                                v_return_message AS return_message,
                                NULL AS data;
                            LEAVE proc_label;
                        END IF;
                    END IF;

                    -- =====================
                    -- Check duplicate user by email
                    -- =====================
                    SELECT COUNT(user_id)
                    INTO v_duplicate_count
                    FROM ms_users
                    WHERE email = p_email
                    AND is_active = '1';

                    IF v_duplicate_count > 0 THEN
                        SET v_return_code = 409;
                        SET v_return_message = 'User with this email already exists';

                        SELECT 
                            v_return_code AS return_code,
                            v_return_message AS return_message,
                            NULL AS data;
                        LEAVE proc_label;
                    END IF;

                    -- =====================
                    -- Generate ID
                    -- =====================
                    SELECT fn_gen_number(v_screen_id)
                    INTO v_generated_id;

                    -- =====================
                    -- Insert data
                    -- =====================
                    INSERT INTO ms_users (
                        user_id,
                        dealer_id,
                        name,
                        email,
                        full_name,
                        password,
                        phone,
                        created_by,
                        created_date,
                        unique_id,
                        is_active
                    ) VALUES (
                        v_generated_id,
                        p_dealer_id,
                        p_name,
                        p_email,
                        p_full_name,
                        p_password,
                        p_phone,
                        p_created_by,
                        NOW(),
                        p_unique_id,
                        '1'
                    );

                    -- =====================
                    -- Return success
                    -- =====================
                    SELECT 
                        v_return_code AS return_code,
                        v_return_message AS return_message,
                        user_id,
                        dealer_id,
                        name,
                        email,
                        full_name,
                        phone,
                        created_by,
                        created_date,
                        unique_id
                    FROM ms_users
                    WHERE user_id = v_generated_id
                    AND is_active = '1'
                    LIMIT 1;
                END;
            END;
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_add_ms_user");
    }
};
