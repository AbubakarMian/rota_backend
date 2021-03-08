<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonthlyRotaDoctors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monthly_rota_doctors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('doctor_id')->nullable()->default(0);
            $table->bigInteger('monthly_rota_id')->nullable()->default(0);
            $table->integer('total_morning')->default(0);
            $table->integer('total_evening')->default(0);
            $table->integer('total_night')->default(0);
            $table->integer('total_duties')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monthly_rota_doctors');
    }
}
