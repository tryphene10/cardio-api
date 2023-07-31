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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('ville_id')->nullable();
            $table->string('name');
            $table->string('surname')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('quartier')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->string('ref')->unique()->nullable();
            $table->string('alias')->unique()->nullable();
            $table->boolean('published');
            $table->string('confirmation_token')->nullable();
            $table->string('activation_code')->nullable();
            $table->dateTime('activation_date')->nullable();
            $table->dateTime('deactivation_date')->nullable();
            $table->rememberToken();
            $table->foreign('ville_id')->references('id')->on('villes');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('role_id')->references('id')->on('roles');
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
        Schema::dropIfExists('users');
    }
};
