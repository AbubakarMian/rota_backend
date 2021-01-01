<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempMonthlyRotaDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_monthly_rota_doctors', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->length(20);
            $table->bigInteger('year')->nullable()->default(null);
            $table->bigInteger('month')->nullable()->default(null);
            $table->Integer('demo_num')->default(1);
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
        Schema::dropIfExists('temp_monthly_rota_doctors');
    }
}
