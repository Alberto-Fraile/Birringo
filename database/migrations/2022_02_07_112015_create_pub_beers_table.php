<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePubBeersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pub_beers', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('beer_id');
            $table->unsignedBigInteger('pub_id');
            $table->foreign('beer_id')->references('id')->on('beers');
            $table->foreign('pub_id')->references('id')->on('pubs');
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
        Schema::dropIfExists('pub_beers');
    }
}
