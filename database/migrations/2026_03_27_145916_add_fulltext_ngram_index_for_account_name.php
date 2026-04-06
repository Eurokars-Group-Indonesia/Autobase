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
        // Drop existing B-TREE index on account_name
        try {
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_account_name');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        // Drop existing NGRAM FULLTEXT index if it exists
        try {
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_account_name_ngram');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        // Create FULLTEXT index without ngram parser for production compatibility
        // This works on all MySQL versions without requiring NGRAM parser support
        // Wildcard matching in queries will provide partial word matching functionality
        DB::statement('CREATE FULLTEXT INDEX idx_account_name_fulltext ON tx_header(account_name)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FULLTEXT index
        try {
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_account_name_fulltext');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        // Recreate B-TREE index
        DB::statement('CREATE INDEX idx_account_name ON tx_header(account_name)');
    }
};
