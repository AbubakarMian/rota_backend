<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveShiftFromRotaGeneratePattern extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rota_generate_pattern', function (Blueprint $table) {
            $table->dropColumn('shift');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rota_generate_pattern', function (Blueprint $table) {
            //
        });
    }
}
