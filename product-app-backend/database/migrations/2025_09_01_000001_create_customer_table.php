<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerTable extends Migration
{
    public function up()
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('profile_image')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('address')->nullable();
            $table->string('status')->default('inactive');
            $table->integer('role');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('email_verified')->default(false);
            $table->string('otp_code')->nullable();
            $table->string('parent_email')->nullable();
            $table->string('parent_otp')->nullable();
            $table->boolean('parent_verified')->default(false);
            $table->string('login_otp')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer');
    }
}
