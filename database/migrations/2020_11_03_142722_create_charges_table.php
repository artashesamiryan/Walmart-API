<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_line_id');
            $table->foreign('order_line_id')->references('id')->on('order_lines')->onDelete('cascade')->onUpdate('cascade');
            $table->string('chargeType');
            $table->string('chargeName');
            $table->string('chargeCurrency');
            $table->string('chargeAmount');
            $table->string('taxName')->nullable();
            $table->string('taxAmount')->nullable();
            $table->string('taxCurrency')->nullable();
            $table->string('refundReason')->nullable();
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
        Schema::dropIfExists('charges');
    }
}
