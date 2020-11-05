<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderLineRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_line_refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_line_id');
            $table->foreign('order_line_id')->references('id')->on('order_lines');
            $table->string('refundId')->nullable();
            $table->string('refundComments')->nullable();
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
        Schema::dropIfExists('order_line_refunds');
    }
}
