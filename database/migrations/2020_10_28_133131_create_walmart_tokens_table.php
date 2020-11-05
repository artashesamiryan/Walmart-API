<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalmartTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('walmart_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('client_id');
            $table->text('client_secret');
            $table->text('token');
            $table->string('expires_at');
            $table->text('refresh_token');
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
        Schema::dropIfExists('walmart_tokens');
    }
}
