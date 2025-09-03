<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'profile_image')) {
                $table->dropColumn('profile_image');
            }
            
            \DB::statement("UPDATE customers SET role = 'user' WHERE role = 'adult' OR role IS NULL");
            \DB::statement("UPDATE customers SET role = 'user' WHERE role = 'child'");
        });
        
        Schema::table('userauth', function (Blueprint $table) {
            \DB::statement("UPDATE userauth SET user_role = 'user' WHERE user_role = 'adult' OR user_role IS NULL");
            \DB::statement("UPDATE userauth SET user_role = 'user' WHERE user_role = 'child'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('profile_image')->nullable()->after('profile_picture');
        });
        
    }
};
