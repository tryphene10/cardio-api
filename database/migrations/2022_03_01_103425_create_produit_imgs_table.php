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
        Schema::create('produit_imgs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('produit_id');
            $table->string('name');
            $table->string('ref')->unique();
            $table->string('alias');
            $table->boolean('published')->default(1);
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
        Schema::dropIfExists('produit_imgs');
    }
};
