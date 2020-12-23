<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRotaAddRotaGeneratePatternId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rota', function (Blueprint $table) {
            $table->bigInteger('rota_generate_pattern_id')->nullable()->default(0);
            $table->tinyInteger('is_ucc')->nullable()->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
