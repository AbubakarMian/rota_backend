<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class General_rota_request extends Model
{
    use SoftDeletes;

    public $timestamps = false;
    protected $table='general_rota_request';

    public function doctor(){
        return $this->hasOne('App\models\Doctor','id','doctor_id')->withTrashed();
    }


}
