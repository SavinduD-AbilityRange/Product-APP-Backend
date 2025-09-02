<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_interests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('interest_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('interest_id')->references('id')->on('interests')->onDelete('cascade');
            $table->unique(['user_id', 'interest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_interests');
    }
};
