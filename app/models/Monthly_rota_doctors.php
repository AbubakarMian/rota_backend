<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Monthly_Rota_doctors extends Model
{

    use SoftDeletes;
    protected $table='monthly_rota_doctors';
}
