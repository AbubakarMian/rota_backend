<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRotaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rota', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('doctor_id')->nullable()->default(0);
            $table->string('shift')->nullable()->default(null);
            $table->bigInteger('duty_date')->nullable()->default(null);
            $table->bigInteger('doctor_type_id')->nullable()->default(0);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rota');
    }
}
