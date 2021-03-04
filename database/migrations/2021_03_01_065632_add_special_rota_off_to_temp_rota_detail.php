<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpecialRotaOffToTempRotaDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_rota_detail', function (Blueprint $table) {
            $table->string('special_rota_off')->nullable()->default(null);
            $table->string('conditions')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_rota_detail', function (Blueprint $table) {
            //
        });
    }
}
