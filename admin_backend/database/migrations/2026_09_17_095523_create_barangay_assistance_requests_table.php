<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('barangay_assistance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();

            // Stored as strings, matching how incidents.barangay already
            // works elsewhere in this app — no FK to a Barangay model,
            // consistent with the rest of the scoping we've built so far.
            $table->string('requesting_barangay');
            $table->string('target_barangay');

            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('responded_by')->nullable()->constrained('users');

            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->text('decline_reason')->nullable();
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('barangay_assistance_requests');
    }
};