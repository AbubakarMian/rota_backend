<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor_type extends Model
{
    use SoftDeletes;
    protected $table='doctor_type';
}
