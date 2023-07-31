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
        Schema::create('kit_produits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('produit_id');
            $table->unsignedBigInteger('kit_id');
            $table->unsignedBigInteger('element_id');
            $table->string('ref')->unique();
            $table->string('alias');
            $table->boolean('published')->default(1);
            $table->foreign('element_id')->references('id')->on('elements');
            $table->foreign('kit_id')->references('id')->on('produits');
            $table->foreign('produit_id')->references('id')->on('produits');
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
        Schema::dropIfExists('kit_produits');
    }
};
