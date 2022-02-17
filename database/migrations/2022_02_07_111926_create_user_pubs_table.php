<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPubsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_pubs', function (Blueprint $table) {
            $table->id('id');
            //Solo lo veo necesario si un usuario quiere añadir un pub a favoritos y no lo tenemos organizado asi en la app, 
            //habria que añadir y diseñar mas pantallas por lo que lo veo como opcion mas adelante en todo caso.
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
        Schema::dropIfExists('user_pubs');
    }
}
