<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->date('date_of_birth');
            $table->integer('role'); // 1 admin, 2 manager, 3 user
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('otp_code', 6)->nullable();
            $table->string('status', 20)->default('inactive'); // 1 active 0 inactive
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
