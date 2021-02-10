<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use SoftDeletes;
    protected $table='doctor';


    public function user(){
        return $this->hasOne('App\models\User','id','user_id')->withTrashed();
    }

    public function doctor_type(){
        return $this->hasOne('App\models\Doctor_type','id','doctor_type_id');
    }

    public function general_rota_morning(){
        return $this->hasOne('App\models\General_rota_request','doctor_id','id')->where('shift','morning');
    }

    public function general_rota_evening(){
        return $this->hasOne('App\models\General_rota_request','doctor_id','id')->where('shift','evening');
    }

    public function general_rota_night(){
        return $this->hasOne('App\models\General_rota_request','doctor_id','id')->where('shift','night');
    }



}
