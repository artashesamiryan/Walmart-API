<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderLineTrackingInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_line_tracking_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_line_id');
            $table->foreign('order_line_id')->references('id')->on('order_lines');
            $table->string('shipDateTime');
            $table->string('carrier');
            $table->string('otherCarrier');
            $table->string('methodCode');
            $table->string('carrierMethodCode');
            $table->string('trackingNumber');
            $table->text('trackingURL');
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
        Schema::dropIfExists('order_line_tracking_infos');
    }
}
