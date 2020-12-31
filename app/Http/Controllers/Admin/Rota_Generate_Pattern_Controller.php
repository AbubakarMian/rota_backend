<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\models\Doctor;
use App\models\Leave_Request;
use App\models\User;
use App\models\Weekday;
use App\models\Monthly_rota;
use Illuminate\Support\Facades\Response;
use LeaveRequest;
use App\models\Rota_Generate_Pattern;




class Rota_Generate_Pattern_Controller extends Controller
{
    public function index()
    {

        // $list = Monthly_rota::get();
        // $total_days = cal_days_in_month(CAL_GREGORIAN, $list->month, $list->year)->paginate(10);
        // return view('admin.rota_generate_pattern.index');

        $list = Rota_Generate_Pattern::get();
        return view('admin.rota_generate_pattern.index');


    }






    public function create()
    {
        $control = 'create';
        $generate = Rota_Generate_Pattern::get();

        return \View::make(
            'admin.rota_generate_pattern.create',
            compact('control', 'generate')
        );
    }




    public function save(Request $request){

        $generate = new Rota_Generate_Pattern();
}


}
