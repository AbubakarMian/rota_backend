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
use Illuminate\Support\Facades\Config;

class Special_Rota_Request_Controller extends Controller
{


    public function index(Request $request)
    {
       // dd($list[0]->doctor->user);
        // $doctor = Doctor::with(['user'])->get();
        $name = $request->name ?? '';
        $list = Special_rota_request::whereHas('doctor', function($query) use($name){
       $query->whereHas('user', function($k) use($name){
        $k->where('name','like','%'.$name.'%');
       });

        })->paginate(10);

        return \View::make('admin.special_rota_request.index', compact('list','name'));
    }


    public function create()
    {
        $control = 'create';
        $special = Special_rota_request::get();
        $doctor = Doctor::with(['user'])->get();
        $shifts = Config::get('constants.duty_type_request');

        return \View::make(
            'admin.special_rota_request.create',
            compact('control', 'special', 'doctor','shifts')
        );
    }

    public function save(Request $request)
    {

        $generate = new Special_rota_request();
        $generate ->duty_date=strtotime($request->dutydate);
        $generate ->shift=$request->shiftday;
        $generate ->doctor_id=$request->doctor_id;
        if ($request->duty == 'wantduty') {
            $generate->want_duty = true;
        }
        elseif ($request->duty == 'wantoff') {
            $generate->want_off = true;

        }
        if ($request->annual == 'annual_leave') {
            $generate->annual_leave = 1;
        }


       $generate ->Save();
        return Redirect('admin/special/rota');
    }



    public function search(Request $request)
    {
        $doctors = Doctor::whereHas('user', function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->name . '%');
        })->with(['user' => function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }])->paginate(13);
        return view('admin.doctor.index', compact('doctors'));
    }

    public function destroy_undestroy($id)
    {

        $special = Special_rota_request::find($id);
        if ($special) {
            Special_rota_request::destroy($id);
            $new_value = 'Activate';
        } else {
            Special_rota_request::withTrashed()->find($id)->restore();
            $new_value = 'Delete';
        }
        $response = Response::json([
            "status" => true,
            'action' => Config::get('constants.ajax_action.delete'),
            'new_value' => $new_value
        ]);
        return $response;
    }
}
