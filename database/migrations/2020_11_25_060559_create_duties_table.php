<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDutiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('duties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('doctor_id')->nullable()->default(0);
            $table->date('duty_date')->nullable()->default(null);
            $table->date('week_day_id')->nullable()->default(null);
            $table->tinyInteger('is_evening')->nullable()->default(0);
            $table->tinyInteger('is_morning')->nullable()->default(0);
            $table->tinyInteger('is_night')->nullable()->default(0);
            $table->tinyInteger('is_cc')->nullable()->default(0);
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
        Schema::dropIfExists('duties');
    }
}
