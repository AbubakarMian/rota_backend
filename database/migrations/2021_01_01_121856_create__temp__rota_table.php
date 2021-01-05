<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempRotaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_rota', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('doctor_id')->nullable()->default(0);
            $table->bigInteger('monthly_rota_id')->nullable()->default(0);
            $table->string('shift')->nullable()->default(null);
            $table->bigInteger('duty_date')->nullable()->default(null);
            $table->bigInteger('doctor_type_id')->nullable()->default(0);
            $table->tinyInteger('is_ucc')->nullable()->default(0);
            $table->integer('demo_num')->default(1);
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
        Schema::dropIfExists('_temp__rota');
    }
}
