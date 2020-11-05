<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderLineStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_line_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_line_id');
            $table->foreign('order_line_id')->references('id')->on('order_lines')->onDelete('cascade')->onUpdate('cascade');
            $table->string('status');
            $table->string('unitOfMeasurement');
            $table->string('amount');
            $table->string('cancellationReason')->nullable();
            $table->string('trackingInfo')->nullable();
            $table->string('returnCenterAddress')->nullable();
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
        Schema::dropIfExists('order_line_statuses');
    }
}
