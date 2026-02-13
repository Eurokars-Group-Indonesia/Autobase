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
        Schema::create('sq_sequence', function (Blueprint $table) {
            $table->increments('id'); // int(10) AUTO_INCREMENT PRIMARY KEY

            $table->string('screen_id', 10);
            $table->string('seq_name', 100);
            $table->integer('seq_value')->nullable();

            // index
            $table->index('screen_id', 'fk_screen_id_idx');

            // foreign key
            $table->foreign('screen_id', 'fk_screen_id')
                ->references('screen_id')
                ->on('ms_counter_number')
                ->onDelete('no action')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sq_sequence');
    }
};
