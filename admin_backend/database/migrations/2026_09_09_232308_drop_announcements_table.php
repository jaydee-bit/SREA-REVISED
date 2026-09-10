<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('announcements');
    }

    public function down()
    {
        // Intentionally left blank - the Announcements feature has been
        // removed. If it's ever restored, re-run the original
        // 2026_04_29_075559_create_announcements_table migration instead
        // of rebuilding the schema here.
    }
};