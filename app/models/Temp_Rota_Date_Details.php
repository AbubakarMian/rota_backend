<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Temp_Rota_Date_Details extends Model
{
    use SoftDeletes;
    protected $table='temp_rota_date_details';
}
