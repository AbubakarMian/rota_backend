<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Temp_Rota_detail extends Model
{
    use SoftDeletes;
    protected $table='temp_rota_detail';

    public function doctor(){
        return $this->hasOne('App\models\Doctor','id','doctor_id')->withTrashed();
    }
    public function temp_rota(){
        return $this->hasOne('App\models\TempRota','id','temp_rota_id')->withTrashed();
    }
}
