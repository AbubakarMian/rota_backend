<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TempRota extends Model
{
    use SoftDeletes;
    protected $table='temp_rota';
}
