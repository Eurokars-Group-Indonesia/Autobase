<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ms_counter_number', function (Blueprint $table) {
            // primary key
            $table->string('screen_id', 10)->primary();

            $table->integer('max_digit'); // int(2)
            $table->string('cnt_date', 8)->nullable();
            $table->string('cnt_date_fmt', 8)->nullable();
            $table->string('ptn_prefix', 10)->nullable();
            $table->string('ptn_suffix', 10)->nullable();
            $table->string('curr_value', 15)->nullable();
            $table->string('max_value', 15)->nullable();
            $table->string('min_value', 15)->nullable();

            $table->integer('seq_flg')->default(0);
            $table->string('seq_nm', 30)->nullable();
            $table->string('note', 100)->nullable();
            $table->string('category_cd', 10)->nullable();

            $table->dateTime('ins_ts')->useCurrent();
            $table->string('ins_usr_id', 10)->nullable();

            $table->integer('upd_cntr'); // int(9)
            $table->dateTime('upd_ts')->useCurrent();
            $table->string('upd_usr_id', 10)->nullable();

            $table->string('key_elm', 10)->nullable();
            $table->string('c_cnt_ptn', 10)->default('*');
            $table->integer('digit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms_counter_number');
    }
};
