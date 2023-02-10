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
        Schema::create('contact_details', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->default(null);
            $table->string('mobile', 20)->nullable()->default(null);
            $table->string('contact_person', 191)->nullable()->default(null);
            $table->string('contact_person_number', 20)->nullable()->default(null);
            $table->string('contact_person_address', 254)->nullable()->default(null);
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
        Schema::dropIfExists('contact_details');
    }
};
