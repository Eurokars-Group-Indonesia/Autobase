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
        
        // Drop existing NGRAM indexes if they exist
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
        
        // Create FULLTEXT index without ngram parser for production compatibility
        // This works on all MySQL versions without requiring NGRAM parser support
        // Wildcard matching in queries will provide partial word matching functionality
        DB::statement('CREATE FULLTEXT INDEX idx_customer_name_fulltext ON tx_header(customer_name)');
        DB::statement('CREATE FULLTEXT INDEX idx_registration_no_fulltext ON tx_header(registration_no)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop FULLTEXT indexes
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
        
        // Recreate B-TREE indexes
        Schema::table('tx_header', function (Blueprint $table) {
            $table->index('customer_name', 'idx_customer_name');
            $table->index('registration_no', 'idx_registration_no');
        });
    }
};
