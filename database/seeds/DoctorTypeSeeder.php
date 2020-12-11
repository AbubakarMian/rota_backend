<?php

use Illuminate\Database\Seeder;
use App\models\Doctor_type;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        Doctor_type::firstOrCreate([
            'name' => 'resident',
            'id'   => '1',

        ]);;

        Doctor_type::firstOrCreate([
            'name' => 'registrar',
            'id'   => '2',
        ]);;

    }
}
