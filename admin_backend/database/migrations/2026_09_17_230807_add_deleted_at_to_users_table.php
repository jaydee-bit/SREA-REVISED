<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds deleted_at so deleting a staff account with historical activity
     * (incidents reported/assigned, assistance requests made/responded to,
     * responder profiles, etc.) no longer fails on a foreign key
     * constraint — the account is soft-deleted instead of the row being
     * removed outright, so every existing users.id reference elsewhere
     * stays valid.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};