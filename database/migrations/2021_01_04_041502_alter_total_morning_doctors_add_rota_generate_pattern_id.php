<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTotalMorningDoctorsAddRotaGeneratePatternId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rota_generate_pattern', function (Blueprint $table) {
            $table->integer('total_morning_doctors')->default(0);
            $table->integer('total_evening_doctors')->default(0);
            $table->integer('total_night_doctors')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
