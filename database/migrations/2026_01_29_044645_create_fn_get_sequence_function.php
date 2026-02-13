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

            DROP function IF EXISTS fn_get_sequence;

            CREATE FUNCTION `fn_get_sequence`(
                `p_screen_id` VARCHAR(10)
            )
            RETURNS int(11)
            LANGUAGE SQL
            DETERMINISTIC
            CONTAINS SQL
            SQL SECURITY INVOKER
            COMMENT ''
            BEGIN
                    DECLARE p_seq_value INT;

                    SELECT COALESCE(MAX(seq_value), 0) + 1 INTO p_seq_value
                    FROM sq_sequence
                    WHERE screen_id = p_screen_id;

                    UPDATE sq_sequence
                    SET seq_value = p_seq_value
                    WHERE screen_id = p_screen_id;

                    RETURN p_seq_value;
                END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // DB::unprepared("DROP FUNCTION IF EXISTS `fn_get_sequence`");

        // Schema::dropIfExists('fn_get_sequence');

        DB::unprepared("drop function fn_get_sequence");

        // DB::unprepared("DROP TRIGGER IF EXISTS `tg_ms_user_roles_AFTER_INSERT`");
    }
};
