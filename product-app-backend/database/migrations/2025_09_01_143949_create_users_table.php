<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->date('dob');
            $table->string('address');
            $table->string('profile_picture')->nullable();
            $table->string('parent_email')->nullable();
            $table->integer('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->integer('parent_otp')->nullable();
            $table->timestamp('parent_otp_expires_at')->nullable();
            $table->boolean('is_parent_verified')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};