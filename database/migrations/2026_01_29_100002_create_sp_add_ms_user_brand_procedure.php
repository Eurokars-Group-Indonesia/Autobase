<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_add_ms_user_brand;

            CREATE PROCEDURE sp_add_ms_user_brand(
                IN p_user_id VARCHAR(50),
                IN p_brand_id VARCHAR(50),
                IN p_created_by VARCHAR(50),
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
                DECLARE v_exists_brand INT DEFAULT 0;
                DECLARE v_exists_created_by INT DEFAULT 0;
                DECLARE v_screen_id VARCHAR(25) DEFAULT 'MUB01';

                proc_label: BEGIN
                    -- =====================
                    -- Check created_by user exists
                    -- =====================
                    SELECT COUNT(user_id)
                    INTO v_exists_created_by
                    FROM ms_users
                    WHERE user_id = p_created_by
                    AND is_active = '1';

                    IF v_exists_created_by = 0 THEN
                        SET v_return_code = 404;
                        SET v_return_message = 'Created by user not found';

                        SELECT 
                            v_return_code AS return_code,
                            v_return_message AS return_message,
                            NULL AS data;
                        LEAVE proc_label;
                    END IF;

                    -- =====================
                    -- Check user exists
                    -- =====================
                    SELECT COUNT(user_id)
                    INTO v_exists_user
                    FROM ms_users
                    WHERE user_id = p_user_id
                    AND is_active = '1';

                    IF v_exists_user = 0 THEN
                        SET v_return_code = 404;
                        SET v_return_message = 'User not found';

                        SELECT 
                            v_return_code AS return_code,
                            v_return_message AS return_message,
                            NULL AS data;
                        LEAVE proc_label;
                    END IF;

                    -- =====================
                    -- Check brand exists
                    -- =====================
                    SELECT COUNT(brand_id)
                    INTO v_exists_brand
                    FROM ms_brand
                    WHERE brand_id = p_brand_id
                    AND is_active = '1';

                    IF v_exists_brand = 0 THEN
                        SET v_return_code = 404;
                        SET v_return_message = 'Brand not found';

                        SELECT 
                            v_return_code AS return_code,
                            v_return_message AS return_message,
                            NULL AS data;
                        LEAVE proc_label;
                    END IF;

                    -- =====================
                    -- Check duplicate user brand
                    -- =====================
                    SELECT COUNT(user_brand_id)
                    INTO v_duplicate_count
                    FROM ms_user_brand
                    WHERE user_id = p_user_id
                    AND brand_id = p_brand_id
                    AND is_active = '1';

                    IF v_duplicate_count > 0 THEN
                        SET v_return_code = 409;
                        SET v_return_message = 'User brand already assigned';

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
                    INSERT INTO ms_user_brand (
                        user_brand_id,
                        user_id,
                        brand_id,
                        created_by,
                        created_date,
                        unique_id,
                        is_active
                    ) VALUES (
                        v_generated_id,
                        p_user_id,
                        p_brand_id,
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
                        user_brand_id,
                        user_id,
                        brand_id,
                        created_by,
                        created_date,
                        unique_id,
                        is_active
                    FROM ms_user_brand
                    WHERE user_brand_id = v_generated_id
                    AND is_active = '1'
                    LIMIT 1;
                END;
            END;
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_add_ms_user_brand");
    }
};
