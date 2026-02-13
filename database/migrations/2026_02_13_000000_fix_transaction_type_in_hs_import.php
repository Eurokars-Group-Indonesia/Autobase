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
        // This migration fixes any incorrect transaction_type values
        // that might have been set to 'H' when they should be 'B'
        
        // Note: This is a data fix migration
        // We cannot automatically determine which records should be 'B' vs 'H'
        // So we just ensure the column has proper default value
        
        // If you need to fix existing data, you can uncomment and modify this:
        /*
        DB::table('hs_import')
            ->where('transaction_type', 'H')
            ->where('some_condition_to_identify_body_imports', true)
            ->update(['transaction_type' => 'B']);
        */
        
        // Ensure the column has default value
        Schema::table('hs_import', function (Blueprint $table) {
            $table->enum('transaction_type', ['H', 'B'])->default('H')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse, this is a fix migration
    }
};
