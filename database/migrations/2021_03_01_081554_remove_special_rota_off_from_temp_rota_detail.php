<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveSpecialRotaOffFromTempRotaDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_rota_detail', function (Blueprint $table) {
            $table->dropColumn('conditions');
            $table->dropColumn('special_rota_off');
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
