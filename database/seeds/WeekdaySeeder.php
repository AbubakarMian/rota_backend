<?php

use Illuminate\Database\Seeder;
use App\models\Weekday;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WeekdaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        Weekday::firstOrCreate([
            'name' => 'Sunday',
            'id'   => '1',

        ]);;

        Weekday::firstOrCreate([
            'name' => 'Monday',
            'id'   => '2',
        ]);;

        Weekday::firstOrCreate([
            'name' => 'Tuesday',
            'id'   => '3',
        ]);;
        Weekday::firstOrCreate([
            'name' => 'Wednesday',
            'id'   => '4',
        ]);;
        Weekday::firstOrCreate([
            'name' => 'Thursday',
            'id'   => '5',
        ]);
        Weekday::firstOrCreate([
            'name' => 'Friday',
            'id'   => '6',
        ]);
        Weekday::firstOrCreate([
            'name' => 'Saturday',
            'id'   => '7',
        ]);


    }
}
