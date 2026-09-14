<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * There is no role/permission concept in the app today — every account
     * is implicitly a full admin. Every row that exists at migration time
     * is therefore promoted to superadmin so no one is locked out; new
     * accounts created afterward default to the lower-privilege manager
     * role via the column default.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('manager')->after('password');
        });

        DB::table('users')->update(['role' => 'superadmin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
