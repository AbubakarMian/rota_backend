<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Monthly_rota extends Model
{
    use SoftDeletes;
    protected $table='monthly_rota';
}
