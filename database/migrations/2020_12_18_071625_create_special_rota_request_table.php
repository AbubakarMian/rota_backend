<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialRotaRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

            Schema::create('special_rota_request', function (Blueprint $table) {
                $table->unsignedBigInteger('id', true)->length(20);
                $table->bigInteger('doctor_id');
                $table->bigInteger('duty_date')->nullable()->default(null);
                $table->tinyInteger('want_duty')->nullable()->default(0);
                $table->tinyInteger('want_off')->nullable()->default(0);
                $table->string('shift')->nullable()->default(null);
                $table->tinyInteger('annual_leave')->nullable()->default(0);
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
        Schema::dropIfExists('special_rota_request');
    }
}
