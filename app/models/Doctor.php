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
        return $this->hasOne('App\models\Doctor_type','id','doctor_type_id')->withTrashed();
    }



}
