<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // ✅ ADD THIS LINE
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add the uuid column as nullable first
        Schema::table('incidents', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
            $table->index('uuid');
        });

        // Step 2: Generate UUIDs for all existing records
        // DB is now recognized
        $incidents = DB::table('incidents')->whereNull('uuid')->get();
        foreach ($incidents as $incident) {
            DB::table('incidents')
                ->where('id', $incident->id)
                ->update(['uuid' => (string) Str::uuid()]);
        }

        // Step 3: Make uuid non-nullable now that all records have one
        Schema::table('incidents', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropIndex(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
