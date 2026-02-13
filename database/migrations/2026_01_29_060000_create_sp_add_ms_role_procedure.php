<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_add_ms_role;

            CREATE PROCEDURE sp_add_ms_role(
                IN p_role_code VARCHAR(10),
                IN p_role_name VARCHAR(50),
                IN p_role_description VARCHAR(200),
                IN p_user_id VARCHAR(50),
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
                DECLARE v_screen_id VARCHAR(25) DEFAULT 'MRO01';

                proc_label: BEGIN
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
                    -- Check duplicate role
                    -- =====================
                    SELECT COUNT(role_id)
                    INTO v_duplicate_count
                    FROM ms_role
                    WHERE (role_code = p_role_code
                        OR role_name = p_role_name)
                    AND is_active = '1';

                    IF v_duplicate_count > 0 THEN
                        SET v_return_code = 409;
                        SET v_return_message = 'Role already created';

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
                    INSERT INTO ms_role (
                        role_id,
                        role_code,
                        role_name,
                        role_description,
                        created_by,
                        created_date,
                        unique_id
                    ) VALUES (
                        v_generated_id,
                        p_role_code,
                        p_role_name,
                        p_role_description,
                        p_user_id,
                        NOW(),
                        p_unique_id
                    );

                    -- =====================
                    -- Return success
                    -- =====================
                    SELECT 
                        v_return_code AS return_code,
                        v_return_message AS return_message,
                        role_id,
                        role_code,
                        role_name,
                        role_description,
                        created_by,
                        created_date,
                        unique_id
                    FROM ms_role
                    WHERE role_id = v_generated_id
                    AND is_active = '1'
                    LIMIT 1;
                END;
            END;
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_add_ms_role");
    }
};
