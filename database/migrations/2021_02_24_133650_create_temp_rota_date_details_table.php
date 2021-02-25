<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempRotaDateDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_rota_date_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('rota_id')->nullable()->default(0);
            $table->bigInteger('temp_rota_id')->nullable()->default(0);
            $table->bigInteger('date')->nullable()->default(0);
            $table->string('anual_leave_doctor')->nullable()->default(null);
            $table->string('consecutive_doctor')->nullable()->default(null);
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
        Schema::dropIfExists('temp_rota_date_details');
    }
}
