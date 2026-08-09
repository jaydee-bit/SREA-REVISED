<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            // ─── Anonymous support ──────────────────────────────────────────
            // user_id is now nullable (anonymous reports have no user account)
            $table->foreignId('user_id')->nullable()->constrained();

            // ─── Optional reporter name (for responders to fill in) ────────
            $table->string('reporter_name')->nullable();

            // ─── REMOVED (obsolete) ─────────────────────────────────────────
            // persons_involved, reporter_role, reporter_is_verified

            // ─── Incident details ───────────────────────────────────────────
            $table->string('type');
            $table->text('description');
            $table->string('photo_path')->nullable();
            $table->string('barangay');
            $table->string('location_details')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->text('address');

            // ─── Status & timeline ──────────────────────────────────────────
            $table->enum('status', ['Pending', 'Responding', 'Resolved', 'Escalated', 'Rejected'])->default('Pending');
            $table->timestamp('reported_at')->useCurrent();

            // ─── Responder fields ───────────────────────────────────────────
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->text('responder_notes')->nullable();
            $table->text('escalation_reason')->nullable();
            $table->foreignId('escalated_by')->nullable()->constrained('users');
            $table->timestamp('escalated_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('incidents');
    }
};
