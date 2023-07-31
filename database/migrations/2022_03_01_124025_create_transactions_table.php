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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commande_id');
            $table->unsignedBigInteger('mode_id');
            $table->unsignedBigInteger('statut_trans_id');
            $table->string('montant');
            $table->string('paie_phone')->nullable();
            $table->string('taspay_transaction')->nullable();
            $table->string('total_payment');
            $table->string('moyen')->nullable();
            $table->string('image')->nullable();
            $table->boolean('published')->default(1);
            $table->string('ref')->unique();
            $table->string('alias');
            $table->foreign('statut_trans_id')->references('id')->on('statut_transactions');
            $table->foreign('mode_id')->references('id')->on('modes');
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
        Schema::dropIfExists('transactions');
    }
};
