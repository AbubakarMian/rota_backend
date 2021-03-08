<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TempRota extends Model
{
    use SoftDeletes;
    protected $table='temp_rota';

    public function monthly_rota(){
        return $this->hasOne('App\models\Monthly_rota','id','rota_id');
    }

    public function rota_generate_pattern(){
        return $this->hasMany('App\models\Rota_Generate_Pattern','monthly_rota_id','monthly_rota_id');
    }

    public function temp_monthly_rota(){
        return $this->hasMany('App\models\Temp_monthly_rota','temp_rota_id','id');
    }

    public function doctors(){
        return $this->hasMany('App\models\Temp_monthly_rota','temp_rota_id','id');
    }
    public function rota_Date_Detail(){
        return $this->hasMany('App\models\Temp_Rota_Date_Details','temp_rota_id','id');
    }
}
