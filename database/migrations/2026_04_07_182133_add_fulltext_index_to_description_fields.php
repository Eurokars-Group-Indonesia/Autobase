<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add FULLTEXT index to description in tx_header
        DB::statement('ALTER TABLE tx_header ADD FULLTEXT INDEX idx_description_fulltext (description)');

        // Add FULLTEXT index to description in tx_body
        DB::statement('ALTER TABLE tx_body ADD FULLTEXT INDEX idx_description_fulltext (description)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FULLTEXT index from tx_header
        DB::statement('ALTER TABLE tx_header DROP INDEX idx_description_fulltext');

        // Drop FULLTEXT index from tx_body
        DB::statement('ALTER TABLE tx_body DROP INDEX idx_description_fulltext');
    }
};
