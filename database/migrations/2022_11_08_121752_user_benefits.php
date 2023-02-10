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
        Schema::create('user_benefits', function (Blueprint $table) {
            $table->string('gsis', 30)->nullable()->default(null);
            $table->string('sss', 30)->nullable()->default(null);
            $table->string('tin', 30)->nullable()->default(null);
            $table->string('philhealth', 30)->nullable()->default(null);
            $table->string('pension', 20)->nullable()->default(null);
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
        Schema::dropIfExists('user_benefits');
    }
};
