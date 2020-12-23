<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRotaGeneratePatternTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rota_generate_pattern', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->length(20);
            $table->bigInteger('duty_date')->nullable()->default(null);
            $table->bigInteger('monthly_rota_id')->nullable()->default(0);
            $table->string('shift')->nullable()->default(null);
            $table->tinyInteger('has_ucc')->nullable()->default(0);
            $table->bigInteger('total_doctors');
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
        Schema::dropIfExists('rota_generate_pattern');
    }
}
