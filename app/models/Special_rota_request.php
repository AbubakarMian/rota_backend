<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Special_rota_request extends Model
{
    use SoftDeletes;
    protected $table='special_rota_request';

    public function doctor(){
        return $this->hasOne('App\models\Doctor','id','doctor_id')->withTrashed();
    }

}
