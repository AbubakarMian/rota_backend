<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveAnnualLeaveFromSpecialRotaRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('special_rota_request', function (Blueprint $table) {
        //     $table->dropColumn('annual_leave');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('special_rota_request', function (Blueprint $table) {
            //
        });
    }
}
