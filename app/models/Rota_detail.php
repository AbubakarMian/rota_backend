<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rota_detail extends Model
{
    use SoftDeletes;
    protected $table='rota_detail';
}
