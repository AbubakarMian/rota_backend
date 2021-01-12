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
use App\models\General_rota_request;
use App\models\Rota_Request;
use App\models\Special_rota_request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use LeaveRequest;

class Rota_RequestController extends Controller
{

    public function index()
    {

       $doctors = Doctor::with(['user'])->paginate(10);
        return view('admin.rota_request.index', compact('doctors'));
    }
    public function detail($id){

       $leave_request = Leave_Request::where('doctor_id',$id)->paginate(10);
        // $doctors = Doctor::with(['user'])->paginate(13);
        $status = Config::get('constants.ajax_action');
        return view('admin.rota_request.details', compact('leave_request','status'));
    }
    public function status(Request $request,$id){
        $leave_request = Leave_Request::find($id);

        if($request->status == 'accept'){
            $new_status = 'accepted';
        }
        else{
            $new_status = 'rejected';
        }
        $leave_request->status = $new_status;
        $leave_request->save();
 //////////response
        $res = new \stdClass();
        $res->status = true;
        $res->new_value = ucfirst($new_status);
        return json_encode($res);

    }

    public function doctorlist(){


        // $leave = Leave_Request::where('doctor_id',$id)->get();
        //  $doctor = Doctor::find($id);

        //  if(!$leave)


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
        }])->paginate(10);
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

        if($request->startdate > $request->enddate){
            return back()->with('error', 'Invalid Leave request dates');
        }

        $rota = new Leave_Request();
        $rota->start_date = strtotime($request->startdate);
        $rota->end_date = strtotime($request->enddate);
        $rota->doctor_id = $request->doctor_id;

     if ($request->annual == '1') {
        $rota->annual_leave = $request->annual =1;
     }

        $rota->save();
        return redirect('admin/request');
    }


    public function create($id)
    {

       $doctor = Doctor::find($id);
       $doctorlist = Doctor::pluck('id' , 'total_duties');
       $weekdays = Config::get('constants.weekdays');
       $shifts = Config::get('constants.duty_type');

        return \View::make('admin.rota.request.create', compact(
            'doctor',
            'weekdays','shifts','doctor','doctorlist'
        ));
    }


    public function store(Request $request,$doctor_id)
    {

       $Special_rota_request = new Special_rota_request();
       $evening = new Rota_Request();
       $general = new General_rota_request();

        if ($request->is_general == 'general') {
            $general->id = 1;
        }

        $Special_rota_request->duty_date = strtotime($request->dutydate);
        if ($request->shiftday == 'morning') {
            $Special_rota_request->shift='morning' ;
        } elseif ($request->shiftday == 'night') {
            $Special_rota_request->shift='night' ;
        } elseif ($request->shiftday == 'evening') {
            $Special_rota_request->shift='evening' ;
        }

        if ($request->duty == 'wantduty') {
            $Special_rota_request->want_duty = true;
        }
        elseif ($request->duty == 'wantoff') {
            $Special_rota_request->want_off = true;

        }

        $Special_rota_request->save();
        $general->save();



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
