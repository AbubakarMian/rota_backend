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

class Rota_RequestController extends Controller
{

    public function index()
    {

        // $leave = Leave_Request::get();
        // $request = Rota_Request::get();
        $doctors = Doctor::with(['user'])->paginate(10);
        return view('admin.rota_request.index', compact('doctors'));
    }
    public function detail($id){

        $leave_request = Leave_Request::where('doctor_id',$id)->get();
        $doctors = Doctor::with(['user'])->paginate(13);
        return view('admin.rota_request.details', compact('doctors','leave_request'));
    }
    public function request($id){

        $request = Rota_Request::where('doctor_id',$id)->get();
        $doctors = Doctor::with(['user'])->paginate(13);
        return view('admin.rota_request.request_details', compact('doctors','request'));
    }


    public function search(Request $request)
    {


        $doctors = Doctor::whereHas('user', function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->name . '%');
        })->with(['user' => function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }])->paginate(13);
        return view('admin.rota_request.index', compact('doctors'));
    }

    public function leave($id)
    {

        $doctor = Doctor::find($id);
        return \View::make('admin.rota.leave.create', compact(
            'doctor'
        ));
    }


    public function save(Request $request)
    {


        $rota = new Leave_Request();

        $rota->start_date = strtotime($request->startdate);
        $rota->end_date = strtotime($request->enddate);
        $rota->doctor_id = $request->doctor_id;

    // if ($request->annual == 'annual_leave') {
        $rota->annual_leave = $request->annual =1;


        $rota->save();

        return redirect('admin/request');
    }


    public function create($id)
    {

        $doctor = Doctor::find($id);
        $weekdays = Weekday::pluck('name','id');

        return \View::make('admin.rota.request.create', compact(
            'doctor',
            'weekdays'
        ));
    }


    public function store(Request $request,$doctor_id)
    {
        //    return $request;
        // dd($request->dutydate);

        $evening = new Rota_Request();
        $evening->doctor_id = $doctor_id;
        $evening->duty_date = strtotime($request->dutydate);
        $evening->week_day_id = $request->weekday_id;


        if ($request->shiftday == 'general') {
            $evening->is_general = 1;
        } elseif ($request->shiftday == 'morning') {
            $evening->is_morning = 1;
        } elseif ($request->shiftday == 'night') {
            $evening->is_night = 1;
        } elseif ($request->shiftday == 'cc') {
            $evening->is_cc= 1;
        }

        if ($request->duty == 'wantduty') {
            $evening->want_duty = 1;
        }
        elseif ($request->duty == 'wantoff') {
            $evening->want_off = 1;

        }
        if ($request->annual == 'annual_leave') {
            $evening->annual_leave = 1;
        }


        $evening->save();




        return redirect('admin/request');
    }
}

////////////////////modal show leave request (details showing)with ajax
// public function detailmodal($id){

//     $leave_request = Leave_Request::where('doctor_id',$id)->get();
//     $response = Response::json([
//         "status" => true,
//         "msg" => $leave_request
//     ]);
//     return $response;
// } public function detailmodal($id){

//     $leave_request = Leave_Request::where('doctor_id',$id)->get();
//     $response = Response::json([
//         "status" => true,
//         "msg" => $leave_request
//     ]);
//     return $response;
// }
