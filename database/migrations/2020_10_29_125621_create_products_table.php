<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mart')->nullable();
            $table->string('sku')->nullable();
            $table->string('wpid')->nullable();
            $table->string('upc')->nullable();
            $table->string('gtin')->nullable();
            $table->unsignedBigInteger('product_type_id')->nullable();
            $table->foreign('product_type_id')->references('id')->on('product_types')->onDelete('cascade')->onUpdate('cascade');
            $table->string('published_status')->nullable();
            $table->string('lifecycle_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
