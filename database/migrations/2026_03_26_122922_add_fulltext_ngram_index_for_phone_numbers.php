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
        // Drop existing phone number indexes if they exist (MySQL 5.7 compatible)
        try {
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_phone_number_1');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        try {
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_phone_number_2');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        try {
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_phone_number_3');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        try {
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_phone_number_4');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        // Create FULLTEXT index with ngram parser for phone numbers
        // ngram allows partial matching for wildcard searches
        // token_size=3 means it will index every 3-character sequence
        DB::statement('CREATE FULLTEXT INDEX idx_phone_numbers_fulltext ON tx_header(phone_number_1, phone_number_2, phone_number_3, phone_number_4) WITH PARSER ngram');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop ngram fulltext index
        try {
            DB::statement('ALTER TABLE tx_header DROP INDEX idx_phone_numbers_fulltext');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }
        
        // Recreate regular B-TREE indexes
        Schema::table('tx_header', function (Blueprint $table) {
            $table->index('phone_number_1', 'idx_phone_number_1');
            $table->index('phone_number_2', 'idx_phone_number_2');
            $table->index('phone_number_3', 'idx_phone_number_3');
            $table->index('phone_number_4', 'idx_phone_number_4');
        });
    }
};
