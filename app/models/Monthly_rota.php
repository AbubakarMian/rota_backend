<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Monthly_rota extends Model
{
    use SoftDeletes;
    protected $table='monthly_rota';

    public function rota_generate_pattern(){
        return $this->hasMany('App\models\Rota_Generate_Pattern','monthly_rota_id','id');
    }

    public function temp_rota(){
        return $this->hasMany('App\models\TempRota','monthly_rota_id','id');
    }

    public function rota(){
        return $this->hasOne('App\models\Rota','monthly_rota_id','id');
    }
}
