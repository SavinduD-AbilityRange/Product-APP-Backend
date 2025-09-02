<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('userauth', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_role');
            $table->string('user_api_key', 8)->unique();
            $table->string('user_status')->default('active');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('userauth');
    }
};
