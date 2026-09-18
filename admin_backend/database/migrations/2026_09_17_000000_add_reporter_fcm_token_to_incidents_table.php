<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            // The device token captured at submission time, so status-change
            // pushes can reach the reporter directly — anonymous reports have
            // no user_id, so there's no user relation to pull a token from.
            $table->string('reporter_fcm_token')->nullable()->after('contact_number');
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('reporter_fcm_token');
        });
    }
};
