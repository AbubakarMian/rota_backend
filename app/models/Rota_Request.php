<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rota_Request extends Model
{
    use SoftDeletes;
    protected $table='rota_request';


}
