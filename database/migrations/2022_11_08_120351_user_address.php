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
        Schema::create('user_address', function (Blueprint $table) {
            $table->string('region', 20)->nullable()->default(null);
            $table->string('region_name', 191)->nullable()->default(null);
            $table->string('province', 20)->nullable()->default(null);
            $table->string('province_name', 191)->nullable()->default(null);
            $table->string('municipality', 20)->nullable()->default(null);
            $table->string('municipality_name', 191)->nullable()->default(null);
            $table->string('city', 20)->nullable()->default(null);
            $table->string('city_name', 191)->nullable()->default(null);
            $table->string('barangay', 20)->nullable()->default(null);
            $table->string('barangay_name', 191)->nullable()->default(null);
            $table->string('address')->nullable()->default(null);
            $table->string('birth_place')->nullable()->default(null);
            $table->string('district_no', 10)->nullable()->default(null);
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
        Schema::dropIfExists('user_address');
    }
};
