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




}
