<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRotaDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rota_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('doctor_id')->nullable()->default(0);
            $table->bigInteger('total_morning')->nullable()->default(0);
            $table->bigInteger('total_evening')->nullable()->default(0);
            $table->bigInteger('total_night')->nullable()->default(0);
            $table->bigInteger('total_duties')->nullable()->default(0);
            $table->bigInteger('total_leaves')->nullable()->default(0);
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
        Schema::dropIfExists('rota_detail');
    }
}
