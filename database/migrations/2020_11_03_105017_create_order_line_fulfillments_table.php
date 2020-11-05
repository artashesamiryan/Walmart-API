<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderLineFulfillmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_line_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_line_id');
            $table->foreign('order_line_id')->references('id')->on('order_lines')->onDelete('cascade')->onUpdate('cascade');
            $table->string('fulfillmentOption');
            $table->string('shipMethod');
            $table->string('storeId')->nullable();
            $table->string('pickUpDateTime');
            $table->string('pickUpBy')->nullable();
            $table->string('shippingProgramType')->nullable();
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
        Schema::dropIfExists('order_fulfillments');
    }
}
