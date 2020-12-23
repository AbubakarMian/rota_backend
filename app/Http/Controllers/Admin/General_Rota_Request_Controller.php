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
use App\models\Rota_Request;
use Illuminate\Support\Facades\Response;
use LeaveRequest;
use App\models\General_rota_request;

class General_Rota_Request_Controller extends Controller
{


    public function index()
    {


        $list = General_rota_request::with('doctor')->get();
        // dd($list[0]->doctor->user);
        // $doctor = Doctor::with(['user'])->get();

        return \View::make('admin.general_rota_request.index', compact('list'));
    }


    public function create()
    {
        $control = 'create';
        $general = General_rota_request::get();
        $doctor = Doctor::with(['user'])->get();


        return \View::make(
            'admin.general_rota_request.create',
            compact('control', 'general', 'doctor')
        );
    }




    public function save(Request $request)
    {

        $generate = new General_rota_request();
        $generate ->total_duties=$request->total_duties;
        $generate ->shift=$request->shift;
        $generate ->doctor_id=$request->doctor_id;






        $generate ->Save();
        return Redirect('admin/general/rota');
    }
}
