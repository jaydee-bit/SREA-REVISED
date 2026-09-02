<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responder_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('team')->nullable();
            $table->string('vehicle')->nullable();
            $table->enum('current_status', ['Deployed', 'Standby', 'Off Duty'])->default('Off Duty');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responder_profiles');
    }
};