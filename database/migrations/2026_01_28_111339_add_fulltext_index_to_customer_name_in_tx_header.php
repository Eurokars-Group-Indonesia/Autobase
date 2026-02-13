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
        // Check if FULLTEXT index already exists
        $indexes = DB::select("SHOW INDEX FROM tx_header WHERE Key_name = 'idx_customer_name_fulltext'");
        
        if (empty($indexes)) {
            // Add FULLTEXT index
            DB::statement('ALTER TABLE tx_header ADD FULLTEXT INDEX idx_customer_name_fulltext (customer_name)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if FULLTEXT index exists before dropping
        $indexes = DB::select("SHOW INDEX FROM tx_header WHERE Key_name = 'idx_customer_name_fulltext'");
        
        if (!empty($indexes)) {
            // Drop FULLTEXT index
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_customer_name_fulltext');
        }
    }
};
