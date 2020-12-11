<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRotaRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rota_request', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('doctor_id')->nullable()->default(0);
            $table->bigInteger('duty_date')->nullable()->default(null);
            $table->bigInteger('week_day_id')->nullable()->default(null);
            $table->tinyInteger('is_general')->nullable()->default(0);
            $table->tinyInteger('is_evening')->nullable()->default(0);
            $table->tinyInteger('is_morning')->nullable()->default(0);
            $table->tinyInteger('is_night')->nullable()->default(0);
            $table->tinyInteger('is_cc')->nullable()->default(0);
            $table->tinyInteger('want_duty')->nullable()->default(0);
            $table->tinyInteger('want_off')->nullable()->default(0);
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
        Schema::dropIfExists('rota_request');
    }
}
