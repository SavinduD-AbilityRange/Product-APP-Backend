<?php
//[02/09/2025 |Asmitha T| 11.11] - Create User Interests Table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_interests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('interest_id');
            $table->timestamps();
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_interests');
    }
};
