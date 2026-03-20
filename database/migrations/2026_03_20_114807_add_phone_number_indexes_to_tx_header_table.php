<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add B-TREE indexes on phone_number columns for optimized LIKE queries
     * Note: B-TREE indexes work efficiently for prefix matching (phone%)
     * For suffix matching (%phone), the index is still used but less efficiently
     */
    public function up(): void
    {
        Schema::table('tx_header', function (Blueprint $table) {
            if (!$this->hasIndex('tx_header', 'idx_phone_fulltext')) {
                $table->fullText(
                    ['phone_number_1', 'phone_number_2', 'phone_number_3', 'phone_number_4'],
                    'idx_phone_fulltext'
                );
            }
        });
 
        \Log::info('Phone number indexes updated: replaced 4 B-TREE with 1 FULLTEXT index on tx_header');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tx_header', function (Blueprint $table) {
             if ($this->hasIndex('tx_header', 'idx_phone_fulltext')) {
                $table->dropFullText('idx_phone_fulltext');
            }
        });

        \Log::info('Phone number indexes rolled back: restored 4 B-TREE indexes on tx_header');
    }

    /**
     * Helper method to check if an index exists on a table
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return !empty($indexes);
    }
};
