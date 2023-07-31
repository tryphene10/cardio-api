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
        Schema::create('paniers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commande_id');
            $table->unsignedBigInteger('produit_id');
            $table->unsignedBigInteger('long_stent_id')->nullable();
            $table->string('quantite');
            $table->string('prix_total');
            $table->string('ref')->unique();
            $table->string('alias');
            $table->boolean('published')->default(1);
            $table->foreign('long_stent_id')->references('id')->on('long_stents');
            $table->foreign('produit_id')->references('id')->on('produits');
            $table->foreign('commande_id')->references('id')->on('commandes');
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
        Schema::dropIfExists('paniers');
    }
};
