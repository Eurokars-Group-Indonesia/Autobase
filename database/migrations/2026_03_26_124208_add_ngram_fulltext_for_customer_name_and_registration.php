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
        // Drop existing FULLTEXT indexes
        try {
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_customer_name_fulltext');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        try {
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_registration_no_fulltext');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        // Create FULLTEXT index with ngram parser for customer_name and registration_no
        // ngram allows partial matching and supports short words (< 4 chars)
        // This will handle searches like "ms dhivi", "mr john", etc.
        DB::statement('CREATE FULLTEXT INDEX idx_customer_name_ngram ON tx_header(customer_name) WITH PARSER ngram');
        DB::statement('CREATE FULLTEXT INDEX idx_registration_no_ngram ON tx_header(registration_no) WITH PARSER ngram');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop ngram fulltext indexes
        try {
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_customer_name_ngram');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        try {
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_registration_no_ngram');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        // Recreate regular FULLTEXT indexes
        DB::statement('CREATE FULLTEXT INDEX idx_customer_name_fulltext ON tx_header(customer_name)');
        DB::statement('CREATE FULLTEXT INDEX idx_registration_no_fulltext ON tx_header(registration_no)');
    }
};
