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
        Schema::table('ms_users', function (Blueprint $table) {
            // Drop password column karena hanya menggunakan SSO Microsoft
            $table->dropColumn('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ms_users', function (Blueprint $table) {
            // Restore password column jika rollback
            $table->string('password')->nullable()->after('email');
        });
    }
};
