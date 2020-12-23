<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rota_Generate_Pattern extends Model
{
    use SoftDeletes;
    protected $table='rota_generate_pattern';
}
