<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mpi_account_users', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('token');
            $table->timestamp('banned_at')->nullable()->after('is_banned');
        });
    }

    public function down(): void
    {
        Schema::table('mpi_account_users', function (Blueprint $table) {
            $table->dropColumn(['is_banned', 'banned_at']);
        });
    }
};
