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
        Schema::create('user_vaccination', function (Blueprint $table) {
            $table->id();
            $table->string('dose', 50)->nullable()->default(null);
            $table->string('vaccine', 254)->nullable()->default(null);
            $table->string('vaccine_card', 254)->nullable()->default(null);
            $table->timestamp('vaccination_date')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_vaccination');
    }
};
