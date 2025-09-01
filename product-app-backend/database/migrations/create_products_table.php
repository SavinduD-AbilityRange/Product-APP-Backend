<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('sku')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
