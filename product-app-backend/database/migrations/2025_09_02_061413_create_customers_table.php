<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('profile_image')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('dob');
            $table->string('address');
            $table->string('status')->nullable();
            $table->string('role')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('profile_picture')->nullable();
            $table->string('parent_email')->nullable();
            $table->string('parent_otp')->nullable();
            $table->timestamp('parent_otp_expires_at')->nullable();
            $table->boolean('is_parent_verified')->nullable();
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
