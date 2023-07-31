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
        Schema::create('detail_paniers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('panier_id');
            $table->unsignedBigInteger('produit_id')->nullable();
            $table->unsignedBigInteger('long_stent_id')->nullable();
            $table->string('quantite')->nullable();
            $table->string('prix_total');
            $table->boolean('published');
            $table->string('ref')->unique();
            $table->string('alias');
            $table->foreign('long_stent_id')->references('id')->on('long_stents');
            $table->foreign('produit_id')->references('id')->on('produits');
            $table->foreign('panier_id')->references('id')->on('paniers');
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
        Schema::dropIfExists('detail_paniers');
    }
};
