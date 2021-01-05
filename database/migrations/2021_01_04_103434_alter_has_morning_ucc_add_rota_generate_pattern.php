<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterHasMorningUccAddRotaGeneratePattern extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rota_generate_pattern', function (Blueprint $table) {
        $table->tinyinteger('has_morning_ucc')->nullable()->default(0);
        $table->tinyinteger('has_evening_ucc')->nullable()->default(0);
        $table->tinyinteger('has_night_ucc')->nullable()->default(0);

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
