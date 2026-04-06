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
        
        // Create FULLTEXT index with ngram parser for account_name
        // ngram allows partial matching and supports short words (< 4 chars)
        // This will handle searches like "mr john", "ms dhivi", etc.
        DB::statement('CREATE FULLTEXT INDEX idx_account_name_ngram ON tx_header(account_name) WITH PARSER ngram');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop ngram fulltext index
        try {
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_account_name_ngram');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        // Recreate B-TREE index
        DB::statement('CREATE INDEX idx_account_name ON tx_header(account_name)');
    }
};
