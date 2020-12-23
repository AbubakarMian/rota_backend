<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralRotaRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('general_rota_request', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->length(20);
            $table->bigInteger('doctor_id');
            $table->string('shift')->nullable()->default(null);
            $table->integer('total_duties')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('general_rota_request');
    }
}
