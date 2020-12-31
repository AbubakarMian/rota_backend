<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveIsMorningFromRotaRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('rota_request', function (Blueprint $table) {
        //     $table->dropColumn('is_general;');
        //     $table->dropColumn('is_morning');
        //     $table->dropColumn('is_night');
        //     $table->dropColumn('is_cc');
        //     $table->dropColumn('is_evening');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rota_request', function (Blueprint $table) {
            //
        });
    }
}
