<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonthlyRotaIdFromRotaDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rota_detail', function (Blueprint $table) {
            $table->bigInteger('monthly_rota_id')->nullable()->default(0);
        });

        // Schema::table('rota_detail', function (Blueprint $table) {
        //     $table->dropColumn('status');
        //     $table->dropColumn('rota_id');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rota_detail', function (Blueprint $table) {
            //
        });
    }
}
