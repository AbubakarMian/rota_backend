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
use App\models\Special_rota_request;

class Special_Rota_Request_Controller extends Controller
{


    public function index()
    {


        $list = Special_rota_request::with('doctor')->get();
        // dd($list[0]->doctor->user);
        // $doctor = Doctor::with(['user'])->get();

        return \View::make('admin.special_rota_request.index', compact('list'));
    }


    public function create()
    {
        $control = 'create';
        $special = Special_rota_request::get();
        $doctor = Doctor::with(['user'])->get();


        return \View::make(
            'admin.special_rota_request.create',
            compact('control', 'special', 'doctor')
        );
    }




    public function save(Request $request)
    {

        $generate = new Special_rota_request();
        $generate ->duty_date=strtotime($request->dutydate);
        $generate ->shift=$request->shift;
        $generate ->doctor_id=$request->doctor_id;
        if ($request->duty == 'wantduty') {
            $generate->want_duty = 1;
        }
        elseif ($request->duty == 'wantoff') {
            $generate->want_off = 1;

        }
        if ($request->annual == 'annual_leave') {
            $generate->annual_leave = 1;
        }








        $generate ->Save();
        return Redirect('admin/special/rota');
    }
}
