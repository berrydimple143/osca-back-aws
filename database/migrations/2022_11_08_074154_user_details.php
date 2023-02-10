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
        Schema::create('user_details', function (Blueprint $table) {
            $table->timestamp('birth_date')->nullable();
            $table->integer('religion')->nullable()->default(null);
            $table->integer('blood_type')->nullable()->default(null);
            $table->integer('education')->nullable()->default(null);
            $table->integer('employment_status')->nullable()->default(null);
            $table->integer('civil_status')->nullable()->default(null);
            $table->integer('gender')->nullable()->default(null);
            $table->string('identification')->nullable()->default(null);
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
        Schema::dropIfExists('user_details');
    }
};
