<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave_Request extends Model
{
    use SoftDeletes;
    protected $table='leave_request';


    public function doctor(){
        return $this->hasOne('App\models\Doctor','id','doctor_id')->withTrashed();
    }
}
