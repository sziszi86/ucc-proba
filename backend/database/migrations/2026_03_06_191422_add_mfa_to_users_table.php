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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_helpdesk_agent')->default(false)->after('email');
            $table->boolean('mfa_enabled')->default(false)->after('password');
            $table->string('mfa_secret')->nullable()->after('mfa_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_helpdesk_agent', 'mfa_enabled', 'mfa_secret']);
        });
    }
};
