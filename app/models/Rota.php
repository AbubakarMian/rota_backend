<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rota extends Model
{
    use SoftDeletes;
    protected $table='rota';

    public function doctor(){
        return $this->hasOne('App\models\Doctor','id','doctor_id')->withTrashed();
    }
    public function monthly_rota(){
        return $this->hasOne('App\models\Monthly_rota','id','rota_id');
    }
    public function rota_generate_pattern(){
        return $this->hasMany('App\models\Rota_Generate_Pattern','monthly_rota_id','monthly_rota_id');
    }
}
