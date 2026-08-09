<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['resident', 'non_resident', 'responder', 'admin'])->default('resident');
            $table->string('barangay')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('phone')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('street')->nullable();
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->string('valid_id_type')->nullable();
            $table->string('valid_id_photo')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'barangay', 'is_verified', 'phone', 'gender',
                'birth_date', 'street', 'province', 'municipality',
                'valid_id_type', 'valid_id_photo'
            ]);
        });
    }
};