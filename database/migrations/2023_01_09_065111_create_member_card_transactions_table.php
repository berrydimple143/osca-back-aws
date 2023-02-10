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
        Schema::create('member_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable()->default(null);
            $table->string('office_released', 100)->nullable()->default(null);
            $table->integer('released_by')->nullable()->default(null);
            $table->timestamp('release_date')->nullable();
            $table->string('remarks', 254)->nullable()->default(null);
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
        Schema::dropIfExists('member_card_transactions');
    }
};
