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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('categorie_id')->nullable();
            $table->string('designation');
            $table->text('description')->nullable();
            $table->string('prix_produit')->nullable();
            $table->string('qte');
            $table->boolean('published')->default(1);
            $table->string('ref')->unique();
            $table->string('alias');
            $table->foreign('categorie_id')->references('id')->on('categories');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('produits');
    }
};
