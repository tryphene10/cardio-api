<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('long_stents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('long_id');
            $table->unsignedBigInteger('stent_id');
            $table->unsignedBigInteger('produit_id');
            $table->string('prix')->nullable();
            $table->string('ref')->unique();
            $table->string('alias');
            $table->boolean('published')->default(1);
            $table->foreign('produit_id')->references('id')->on('produits');
            $table->foreign('stent_id')->references('id')->on('stents');
            $table->foreign('long_id')->references('id')->on('longs');
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
        Schema::dropIfExists('long_stents');
    }
};
