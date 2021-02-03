<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Temp_monthly_rota extends Model
{
    use SoftDeletes;
    protected $table='temp_monthly_rota_doctors';
}
