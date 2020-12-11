<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rota extends Model
{
    // public $timestamps = false;
    use SoftDeletes;
    protected $table='rota';
}
